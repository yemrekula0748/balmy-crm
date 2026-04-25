<?php

namespace App\Http\Controllers\Modules;

use App\Models\Agency;
use App\Models\AgencyContract;
use App\Models\Reservation;
use App\Models\ReservationGuest;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrontDeskReservationController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('reservations',
            ['index'],
            ['show'],
            ['create', 'store'],
            ['edit', 'update'],
            ['destroy']
        );
    }

    public function index()
    {
        $reservations = Reservation::with(['agency', 'roomType', 'room', 'guests'])
            ->orderBy('check_in_date', 'desc')
            ->get();
        return view('modules.onburo.reservations.index', compact('reservations'));
    }

    public function create()
    {
        $agencies  = Agency::orderBy('name')->get();
        $roomTypes = RoomType::orderBy('name')->get();
        $rooms     = Room::with('roomType')->orderBy('room_number')->get();
        $contracts = AgencyContract::with('roomType')->orderBy('id', 'desc')->get();

        $roomsJson = $rooms->map(function ($r) {
            return [
                'id'           => $r->id,
                'room_number'  => $r->room_number,
                'room_type_id' => $r->room_type_id,
                'floor'        => $r->floor,
                'code'         => optional($r->roomType)->code,
            ];
        })->values()->toJson();

        $countriesOptionsHtml = '';
        foreach (\App\Helper\DzHelper::countries() as $label) {
            $countriesOptionsHtml .= '<option value="' . e($label) . '">' . e($label) . '</option>';
        }

        return view('modules.onburo.reservations.create',
            compact('agencies', 'roomTypes', 'rooms', 'contracts', 'roomsJson', 'countriesOptionsHtml'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'agency_id'          => 'required|exists:agencies,id',
            'agency_contract_id' => 'nullable|exists:agency_contracts,id',
            'room_type_id'       => 'required|exists:room_types,id',
            'room_id'            => 'required|exists:rooms,id',
            'check_in_date'      => 'required|date',
            'check_out_date'     => 'required|date|after:check_in_date',
            'check_in_time'      => 'nullable|date_format:H:i',
            'check_out_time'     => 'nullable|date_format:H:i',
            'adults'             => 'required|integer|min:1',
            'children'           => 'required|integer|min:0',
            'babies'             => 'required|integer|min:0',
            'nationality'        => 'nullable|string|max:100',
            'voucher_no'         => 'nullable|string|max:100',
            // Misafirler
            'guests'             => 'required|array|min:1',
            'guests.*.first_name'   => 'required|string|max:100',
            'guests.*.last_name'    => 'required|string|max:100',
            'guests.*.nationality'  => 'nullable|string|max:100',
            'guests.*.gender'       => 'nullable|in:male,female,other',
            'guests.*.birth_date'   => 'nullable|date',
            'guests.*.id_no'        => 'nullable|string|max:50',
            'guests.*.passport_no'  => 'nullable|string|max:50',
            'guests.*.phone'        => 'nullable|string|max:30',
            'guests.*.email'        => 'nullable|email|max:150',
            'guests.*.vehicle_plate'=> 'nullable|string|max:30',
        ]);

        DB::transaction(function () use ($data) {
            $guestsData = $data['guests'];
            unset($data['guests']);

            $data['reservation_no'] = Reservation::generateNo();
            $data['created_by']     = auth()->id();

            $reservation = Reservation::create($data);

            foreach ($guestsData as $i => $guestRow) {
                $guestRow['is_primary'] = ($i === 0);
                $reservation->guests()->create($guestRow);
            }
        });

        return redirect()->route('frontdesk.reservations.index')
            ->with('success', 'Rezervasyon başarıyla oluşturuldu.');
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['agency', 'contract.roomType', 'roomType', 'room', 'guests', 'creator']);
        return view('modules.onburo.reservations.show', compact('reservation'));
    }

    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
        return back()->with('success', 'Rezervasyon silindi.');
    }
}
