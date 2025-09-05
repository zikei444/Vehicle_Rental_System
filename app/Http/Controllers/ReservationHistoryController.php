<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Customer; // if exists
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ReservationHistoryController extends Controller
{
    private $vehicleApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/vehicleApi.php';

    public function index()
    {
        // 1. Get logged-in user ID
        $userId = Auth::id();

        // 2. Fetch customer ID from users table (customers table has user_id)
        $customer = \DB::table('customers')->where('user_id', $userId)->first();
        if (!$customer) {
            return redirect()->back()->with('error', 'No customer profile found.');
        }

        // 3. Fetch reservations for this customer
        $reservations = Reservation::where('customer_id', $customer->id)
            ->orderBy('pickup_date', 'desc')
            ->get();

        // 4. Enrich each reservation with vehicle info via API
        foreach ($reservations as &$res) {
            $vehicleResp = Http::get($this->vehicleApi, [
                'action' => 'get',
                'id' => $res->vehicle_id
            ]);
            $res->vehicle = $vehicleResp->json()['data'] ?? null;
        }

        return view('reservations.history', compact('reservations'));
    }
}
