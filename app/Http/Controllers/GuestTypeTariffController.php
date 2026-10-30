<?php

namespace App\Http\Controllers;

use App\Models\GuestType;
use App\Models\GuestTypeTariff;
use App\Models\RoomType;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestTypeTariffController extends Controller
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index()
    {
        $guestTypes = GuestType::with('tariffs.roomType')->orderBy('priority_order')->get();
        $roomTypes = RoomType::where('is_active', true)->get();
        $tariffs = GuestTypeTariff::with(['guestType', 'roomType'])->get()->groupBy('guest_type_id');

        return view('masters.tariffs.index', compact('guestTypes', 'roomTypes', 'tariffs'));
    }

    public function updateTariffMatrix(Request $request)
    {
        $matrix = $request->input('tariffs', []);

        foreach ($matrix as $guestTypeId => $roomTypeRates) {
            foreach ($roomTypeRates as $roomTypeId => $rate) {
                if ($rate !== null && $rate !== '') {
                    GuestTypeTariff::updateOrCreate(
                        ['guest_type_id' => $guestTypeId, 'room_type_id' => $roomTypeId],
                        ['rate_per_night' => (float)$rate, 'is_active' => true]
                    );
                }
            }
        }

        $this->auditService->log(Auth::user(), 'Updated Category-Wise Tariff Matrix', 'Tariffs', GuestTypeTariff::class);

        return back()->with('success', 'Tariff matrix rates successfully saved.');
    }
}

