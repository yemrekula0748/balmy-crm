<?php

namespace App\Http\Controllers\Modules;

use App\Models\Restaurant;
use App\Models\TableSession;
use Illuminate\Http\Request;

class OrderReportController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission(
            'order_reports',
            ['index'],
            [],
            [],
            [],
            []
        );
    }

    public function index(Request $request)
    {
        $query = TableSession::with([
            'table.restaurant',
            'opener',
            'orders.items',
        ])->orderByDesc('opened_at');

        // Filtre — restoran
        if ($request->restaurant_id) {
            $query->whereHas('table', fn($q) =>
                $q->where('restaurant_id', $request->restaurant_id)
            );
        }

        // Filtre — durum
        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->where('is_open', true);
            } elseif ($request->status === 'closed') {
                $query->where('is_open', false);
            }
        }

        // Filtre — tarih
        if ($request->filled('date_from')) {
            $query->whereDate('opened_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('opened_at', '<=', $request->date_to);
        }

        $sessions     = $query->paginate(30)->withQueryString();
        $restaurants  = Restaurant::orderBy('name')->get();

        $page_title = 'Sipariş Raporları';
        return view('modules.orders.report', compact('sessions', 'restaurants', 'page_title'));
    }
}
