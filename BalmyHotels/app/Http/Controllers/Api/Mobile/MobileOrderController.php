<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\RestaurantTable;
use App\Models\TableSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MobileOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RestaurantOrder::with(['session.table.restaurant', 'items.menuItem'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($this->hasColumn('restaurant_orders', 'status')) {
                $query->where('status', $status);
            } elseif (in_array($status, ['completed', 'closed'], true)) {
                $query->whereHas('session', fn ($sessionQuery) => $sessionQuery->where('is_open', false));
            } else {
                $query->whereHas('session', fn ($sessionQuery) => $sessionQuery->where('is_open', true));
            }
        }

        $orders = $query->paginate(20);

        return response()->json([
            'data' => $orders->getCollection()->map(fn (RestaurantOrder $order) => $this->orderPayload($order))->values(),
            'total' => $orders->total(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'restaurant_id' => ['nullable', 'exists:restaurants,id'],
            'table_name' => ['required', 'string', 'max:100'],
            'guest_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
        ]);

        $order = DB::transaction(function () use ($request, $data) {
            $restaurant = $this->resolveRestaurant($request, $data['restaurant_id'] ?? null);
            $table = RestaurantTable::firstOrCreate(
                ['restaurant_id' => $restaurant->id, 'name' => $data['table_name']],
                ['sort_order' => 0]
            );

            $session = $table->activeSession()->first() ?: TableSession::create([
                'restaurant_table_id' => $table->id,
                'opened_by' => $request->user()->id,
                'opened_at' => now(),
                'is_open' => true,
            ]);

            $total = collect($data['items'])->sum(fn ($item) => ((float) ($item['price'] ?? 0)) * (int) $item['quantity']);

            $order = new RestaurantOrder();
            $order->table_session_id = $session->id;
            $order->course_number = 1;
            $order->note = $data['note'] ?? null;
            $order->created_by = $request->user()->id;

            if ($this->hasColumn('restaurant_orders', 'guest_name')) {
                $order->guest_name = $data['guest_name'] ?? null;
            }
            if ($this->hasColumn('restaurant_orders', 'status')) {
                $order->status = $data['status'] ?? 'pending';
            }
            if ($this->hasColumn('restaurant_orders', 'total')) {
                $order->total = $total;
            }

            $order->save();

            foreach ($data['items'] as $itemData) {
                $item = new RestaurantOrderItem();
                $item->order_id = $order->id;
                $item->quantity = (int) $itemData['quantity'];
                $item->note = $itemData['note'] ?? null;

                if ($this->hasColumn('restaurant_order_items', 'name')) {
                    $item->name = $itemData['name'];
                } else {
                    $item->item_name = $itemData['name'];
                }

                if ($this->hasColumn('restaurant_order_items', 'price')) {
                    $item->price = $itemData['price'] ?? 0;
                } else {
                    $item->unit_price = $itemData['price'] ?? 0;
                }

                $item->save();
            }

            return $order->load(['session.table.restaurant', 'items.menuItem']);
        });

        return response()->json(['data' => $this->orderPayload($order)], 201);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'max:50'],
        ]);

        $order = RestaurantOrder::with(['session.table.restaurant', 'items.menuItem'])->findOrFail($id);

        if ($this->hasColumn('restaurant_orders', 'status')) {
            $order->status = $data['status'];
            $order->save();
        }

        if (in_array($data['status'], ['completed', 'closed', 'paid', 'cancelled'], true) && $order->session?->is_open) {
            $order->session->update([
                'is_open' => false,
                'closed_at' => now(),
            ]);
            $order->load('session.table.restaurant');
        }

        return response()->json(['data' => $this->orderPayload($order, $data['status'])]);
    }

    public function restaurants(): JsonResponse
    {
        $restaurants = Restaurant::with(['branch', 'tables'])
            ->orderBy('name')
            ->get()
            ->map(fn (Restaurant $restaurant) => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'branch_id' => $restaurant->branch_id,
                'branch' => $restaurant->branch ? ['name' => $restaurant->branch->name] : null,
                'tables' => $restaurant->tables->map(fn (RestaurantTable $table) => [
                    'id' => $table->id,
                    'name' => $table->name,
                ])->values(),
            ]);

        return response()->json(['data' => $restaurants]);
    }

    private function orderPayload(RestaurantOrder $order, ?string $statusOverride = null): array
    {
        $order->loadMissing(['session.table', 'items.menuItem']);

        return [
            'id' => $order->id,
            'table' => $order->session?->table ? ['name' => $order->session->table->name] : null,
            'guest_name' => $this->hasColumn('restaurant_orders', 'guest_name') ? ($order->guest_name ?? null) : null,
            'status' => $statusOverride ?: ($this->hasColumn('restaurant_orders', 'status')
                ? ($order->status ?? null)
                : ($order->session?->is_open ? 'pending' : 'completed')),
            'total' => $this->hasColumn('restaurant_orders', 'total')
                ? (float) ($order->total ?? 0)
                : $order->items->sum(fn (RestaurantOrderItem $item) => $this->itemPrice($item) * (int) $item->quantity),
            'created_at' => optional($order->created_at)->toISOString(),
            'items' => $order->items->map(fn (RestaurantOrderItem $item) => [
                'id' => $item->id,
                'name' => $item->name ?? $item->item_name ?? $item->menuItem?->getTitle('tr'),
                'quantity' => (int) $item->quantity,
                'price' => $this->itemPrice($item),
                'note' => $item->note,
            ])->values(),
        ];
    }

    private function itemPrice(RestaurantOrderItem $item): float
    {
        return (float) ($item->price ?? $item->unit_price ?? 0);
    }

    private function resolveRestaurant(Request $request, ?int $restaurantId): Restaurant
    {
        if ($restaurantId) {
            return Restaurant::findOrFail($restaurantId);
        }

        $restaurant = Restaurant::query()
            ->when($request->user()->branch_id, fn ($query) => $query->where('branch_id', $request->user()->branch_id))
            ->orderBy('name')
            ->first();

        return $restaurant ?: Restaurant::first() ?: Restaurant::create([
            'branch_id' => $request->user()->branch_id,
            'name' => 'Mobil Restoran',
            'created_by' => $request->user()->id,
        ]);
    }

    private function hasColumn(string $table, string $column): bool
    {
        static $cache = [];
        $key = "{$table}.{$column}";

        if (! array_key_exists($key, $cache)) {
            $cache[$key] = Schema::hasColumn($table, $column);
        }

        return $cache[$key];
    }
}
