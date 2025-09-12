<?php

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

    // ===========================
    // Controller Actions
    // ===========================

    // public function index(Request $request)
    // {
    //     $useApi = (bool) $request->query('use_api', false);

    //     if ($useApi) {
    //         $response = Http::get(url($this->ratingApi));
    //         $ratings = $response->successful() ? $response->json()['data'] ?? [] : [];
    //     } else {
    //         $ratings = Rating::with(['customer.user', 'vehicle'])->get(); // eager load relationships
    //     }

    //     return view('ratings_admin.index', compact('ratings', 'useApi'));
    // }
        // 显示所有评论（不管 pending/approved/rejected）
        
    // public function index()
    // {
    //     $ratings = Rating::with(['customer.user', 'reservation'])->get();
    //     return view('ratings_admin.index', compact('ratings'));
    // }

    // public function dashboard()
    // {
    //     // 直接取 Observer 已经维护好的字段
    // $vehicles = Vehicle::all(); 

    // return view('ratings_admin.dashboard', compact('vehicles'));
    // }
    // public function dashboard(Request $request)
    // {
    //     // Decide whether to use API (for vehicles only)
    //     $useApi = true; // always true since vehicles must come from API

    //     if ($useApi) {
    //         // $response = Http::timeout(10)->get(url('/api/vehicles'));
    //         $vehicles = $this->vehicleService->all();

    //         if ($response->failed()) {
    //             $vehicles = [];
    //         } else {
    //             $vehicles = $response->json()['data'] ?? [];
    //         }
    //     } else {
    //         // fallback (optional)
    //         $vehicles = Vehicle::all();
    //     }

    //     return view('ratings_admin.dashboard', compact('vehicles'));
    // }
    // 显示所有评论（不管 pending/approved/rejected）
    public function index()
    {
        $ratings = Rating::with(['customer.user', 'reservation'])->get();
        return view('ratings_admin.index', compact('ratings'));
    }
    public function dashboard()
    {
        // 直接取 Observer 已经维护好的字段
    $vehicles = Vehicle::all(); 

    return view('ratings_admin.dashboard', compact('vehicles'));
    }
    // 每辆车的详细评分
    public function vehicleRatingsDetails(Vehicle $vehicle)
    {
        // 使用传入的 $vehicle 对象，不要再 find
        $vehicle->load('ratings.customer.user'); // 加载关联
        return view('ratings_admin.vehicle_ratings_details', compact('vehicle'));
    }



    // public function vehicleRatingsDetails(Request $request, int $vehicleId)
    // {
    //     $useApi = (bool) $request->query('use_api', false);
    //     $vehicle = $this->getVehicleJson($vehicleId, $useApi);

    //     return view('ratings_admin.vehicle_ratings_details', compact('vehicle', 'useApi'));
    // }

    // public function approve(Request $request, int $id)
    // {
    //     $request->validate([
    //         'status' => 'required|in:approved,rejected',
    //     ]);

    //     $useApi = (bool) $request->query('use_api', false);

    //     if ($useApi) {
    //         Http::put(url("{$this->ratingApi}/{$id}/status"), [
    //             'status' => $request->status,
    //         ]);
    //     } else {
    //         $this->ratingService->updateStatus($id, $request->status);
    //     }

    //     return response()->json(['success' => true]);
    // }
        // // 批准评论
    public function approve(Request $request, Rating $rating)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $rating->status = $request->status;
        $rating->save();

        return response()->json(['success' => true]);
    }
    

    // public function reply(Request $request, int $id)
    // {
    //     $request->validate([
    //         'reply' => 'required|string|max:500',
    //     ]);

    //     $useApi = (bool) $request->query('use_api', false);

    //     if ($useApi) {
    //         Http::post(url("{$this->ratingApi}/{$id}/reply"), [
    //             'reply' => $request->reply,
    //         ]);
    //     } else {
    //         $this->ratingService->reply($id, $request->reply);
    //     }

    //     return back()->with('success', 'Reply sent and notification created!');
    // }
        public function reply(Request $request, Rating $rating)
    {
        $request->validate([
            'reply' => 'required|string|max:500',
        ]);

        $rating->adminreply = $request->reply;
        $rating->save(); // Observer will notify customer automatically

        return back()->with('success', 'Reply sent and notification created!');
    }

    // public function destroy(Request $request, int $id)
    // {
    //     $useApi = (bool) $request->query('use_api', false);

    //     if ($useApi) {
    //         Http::delete(url("{$this->ratingApi}/{$id}"));
    //     } else {
    //         $this->ratingService->delete($id);
    //     }

    //     return response()->json(['message' => 'Rating deleted successfully']);
    // }
        public function destroy($id)
    {
        $rating = Rating::find($id);
        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        $rating->delete();
        return response()->json(['message' => 'Rating deleted successfully']);
    }

    // public function reject(Request $request, int $id)
    // {
    //     $useApi = (bool) $request->query('use_api', false);

    //     if ($useApi) {
    //         Http::put(url("{$this->ratingApi}/{$id}/status"), ['status' => 'rejected']);
    //     } else {
    //         $this->ratingService->updateStatus($id, 'rejected');
    //     }

    //     return redirect()->back()->with('success', '评论已拒绝');
    // }
        // 拒绝评论
    public function reject($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Rating Rejected');
    }
}
