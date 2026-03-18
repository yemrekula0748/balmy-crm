<?php

namespace App\Http\Controllers\Modules;

use App\Models\BedType;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FrontDeskRoomController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('rooms',
            ['index'],
            [],
            ['store'],
            ['update'],
            ['destroy']
        );
    }

    public function index()
    {
        $rooms    = Room::with(['roomType', 'bedTypes'])->orderBy('room_number')->get();
        $bedTypes = BedType::orderBy('name')->get();
        return view('modules.onburo.rooms.index', compact('rooms', 'bedTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_number'    => 'required|string|max:20|unique:rooms,room_number',
            'floor'          => 'nullable|string|max:20',
            'block'          => 'nullable|string|max:50',
            'extra_features' => 'nullable|string',
            'description'    => 'nullable|string',
            'images'         => 'nullable|array',
            'images.*'       => 'image|max:5120',
            'bed_type_ids'   => 'nullable|array',
            'bed_type_ids.*' => 'exists:bed_types,id',
        ]);

        $savedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $img) {
                $path = $img->store('rooms', 'public');
                $savedImages[] = $path;
            }
        }
        $data['images'] = $savedImages ?: null;

        $bedTypeIds = $data['bed_type_ids'] ?? [];
        unset($data['bed_type_ids']);

        $room = Room::create($data);

        if ($bedTypeIds) {
            $room->bedTypes()->sync($bedTypeIds);
        }

        return back()->with('success', 'Oda eklendi. No: ' . $room->room_number);
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_number'    => 'required|string|max:20|unique:rooms,room_number,' . $room->id,
            'floor'          => 'nullable|string|max:20',
            'block'          => 'nullable|string|max:50',
            'extra_features' => 'nullable|string',
            'description'    => 'nullable|string',
            'new_images'     => 'nullable|array',
            'new_images.*'   => 'image|max:5120',
            'bed_type_ids'   => 'nullable|array',
            'bed_type_ids.*' => 'exists:bed_types,id',
        ]);

        $existingImages = $room->images ?? [];
        if ($request->hasFile('new_images')) {
            foreach ($request->file('new_images') as $img) {
                $path = $img->store('rooms', 'public');
                $existingImages[] = $path;
            }
        }
        $data['images'] = $existingImages ?: null;

        $bedTypeIds = $data['bed_type_ids'] ?? [];
        unset($data['bed_type_ids']);
        unset($data['new_images']);

        $room->update($data);
        $room->bedTypes()->sync($bedTypeIds);

        return back()->with('success', 'Oda güncellendi.');
    }

    public function destroy(Room $room)
    {
        $room->delete();
        return back()->with('success', 'Oda silindi.');
    }
}
