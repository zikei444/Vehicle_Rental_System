<?php
// STUDENT NAME: Kek Xin Ying
// STUDENT ID: 23WMR14547

namespace App\Http\Controllers;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Services\VehicleService;
use App\Services\RatingService;
use App\Services\UserService;
use App\Models\Rating;
use App\Observers\UserObserver;
use App\Observers\RatingObserver;
use App\Models\User;

class AdminRatingController extends Controller
{
    private VehicleService $vehicleService;
    private RatingService $ratingService;
    private UserService $userService;

    // API endpoints
    private string $vehicleApi = '/api/vehicles';
    private string $ratingApi = '/api/ratings';
    private string $userApi = '/api/user';

    public function __construct(
        VehicleService $vehicleService,
        RatingService $ratingService,
        UserService $userService
    ) {
        $this->vehicleService = $vehicleService;
        $this->ratingService = $ratingService;
        $this->userService = $userService;
    }

    // ===========================
    // Helper Methods
    // ===========================

    private function getVehicles(bool $useApi = false): array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->vehicleApi));
            return $response->json()['data'] ?? [];
        } else {
            $jsonResponse = $this->vehicleService->all();
            if ($jsonResponse instanceof \Illuminate\Http\JsonResponse) {
                return $jsonResponse->getData(true)['data'] ?? [];
            }
            return is_array($jsonResponse) ? $jsonResponse : [];
        }
    }

    private function getVehicleJson(int $vehicleId, bool $useApi = false): ?array
    {
        if ($useApi) {
            $response = Http::timeout(10)->get(url($this->vehicleApi . '/' . $vehicleId));
            if ($response->failed()) return null;
            return $response->json()['data'] ?? null;
        } else {
            $jsonResponse = $this->vehicleService->find($vehicleId);
            if ($jsonResponse instanceof \Illuminate\Http\JsonResponse) {
                return $jsonResponse->getData(true)['data'] ?? null;
            }
            return is_array($jsonResponse) ? $jsonResponse : null;
        }
    }

    private function getRatings(?int $id = null, bool $useApi = false)
    {
        if ($useApi) {
            $response = $id
                ? Http::get(url("{$this->ratingApi}/{$id}"))
                : Http::get(url($this->ratingApi));
            return $response->json()['data'] ?? [];
        } else {
            $data = $id
                ? $this->ratingService->find($id)
                : $this->ratingService->all();

            if ($data instanceof \Illuminate\Http\JsonResponse) {
                return $data->getData(true)['data'] ?? [];
            }
            return is_array($data) ? $data : [];
        }
    }

    // show commend pending/approved/rejected
    public function index()
    {
        $ratings = Rating::with(['customer.user', 'reservation'])->get();
        return view('ratings_admin.index', compact('ratings'));
    }
    public function dashboard()
    {
   //get from Observer 
    $vehicles = Vehicle::all(); 

    return view('ratings_admin.dashboard', compact('vehicles'));
    }
    // show vehicle rating details
    public function vehicleRatingsDetails(Vehicle $vehicle)
    {
        // use pass $vehicle 
        $vehicle->load('ratings.customer.user'); //load customer from vehicle
        return view('ratings_admin.vehicle_ratings_details', compact('vehicle'));
    }



        //approvec rating
    public function approve(Request $request, Rating $rating)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $rating->status = $request->status;
        $rating->save();

        return response()->json(['success' => true]);
    }
    
//reply rating
        public function reply(Request $request, Rating $rating)
    {
        $request->validate([
            'reply' => 'required|string|max:500',
        ]);

        $rating->adminreply = $request->reply;
        $rating->save(); // Observer will notify customer automatically

        return back()->with('success', 'Reply sent and notification created!');
    }
    //delete rating
        public function destroy($id)
    {
        $rating = Rating::find($id);
        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        $rating->delete();
        return response()->json(['message' => 'Rating deleted successfully']);
    }

        // reject rating
    public function reject($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Rating Rejected');
    }
}
