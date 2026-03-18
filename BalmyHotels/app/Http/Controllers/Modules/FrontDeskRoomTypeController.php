<?php

namespace App\Http\Controllers\Modules;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;

class FrontDeskRoomTypeController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('room_types',
            ['index'],
            [],
            ['store'],
            ['update'],
            ['destroy']
        );
    }

    public function index()
    {
        $roomTypes = RoomType::withCount('rooms')->orderBy('name')->get();
        $allRooms  = Room::orderBy('room_number')->get();
        return view('modules.onburo.room_types.index', compact('roomTypes', 'allRooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150',
            'code'          => 'required|string|max:30|unique:room_types,code',
            'total_rooms'   => 'required|integer|min:0',
            'max_adults'    => 'required|integer|min:1',
            'max_babies'    => 'required|integer|min:0',
            'max_children'  => 'required|integer|min:0',
            'room_ids'      => 'nullable|array',
            'room_ids.*'    => 'exists:rooms,id',
        ]);

        $roomIds = $data['room_ids'] ?? [];
        unset($data['room_ids']);

        $type = RoomType::create($data);

        // Seçilen odaları bu oda tipine ata
        if ($roomIds) {
            Room::whereIn('id', $roomIds)->update(['room_type_id' => $type->id]);
        }

        return back()->with('success', 'Oda tipi eklendi.');
    }

    public function update(Request $request, RoomType $roomType)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:150',
            'code'          => 'required|string|max:30|unique:room_types,code,' . $roomType->id,
            'total_rooms'   => 'required|integer|min:0',
            'max_adults'    => 'required|integer|min:1',
            'max_babies'    => 'required|integer|min:0',
            'max_children'  => 'required|integer|min:0',
            'room_ids'      => 'nullable|array',
            'room_ids.*'    => 'exists:rooms,id',
        ]);

        $roomIds = $data['room_ids'] ?? [];
        unset($data['room_ids']);

        $roomType->update($data);

        // Önce bu tipten çıkar, sonra yeniden ata
        Room::where('room_type_id', $roomType->id)->update(['room_type_id' => null]);
        if ($roomIds) {
            Room::whereIn('id', $roomIds)->update(['room_type_id' => $roomType->id]);
        }

        return back()->with('success', 'Oda tipi güncellendi.');
    }

    public function destroy(RoomType $roomType)
    {
        $roomType->delete();
        return back()->with('success', 'Oda tipi silindi.');
    }
}
