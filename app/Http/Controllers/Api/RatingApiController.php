<?php

// app/Http/Controllers/Api/RatingApiController.php
namespace App\Http\Controllers\Api;
use App\Models\Rating; // ✅ 加上这一行
use App\Http\Controllers\Controller;
use App\Services\RatingService;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class RatingApiController extends Controller {
    protected $ratingService;

    public function __construct(RatingService $ratingService) {
        $this->ratingService = $ratingService;
    }

    public function summary($vehicleId) {
        return $this->ratingService->getVehicleRatingSummary($vehicleId);
    }

    // 获取审核通过的评论
    public function index($vehicleId) {
        return response()->json($this->ratingService->getApprovedRatings($vehicleId));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id'  => 'required|exists:vehicles,id',
            'rating'      => 'required|integer|min:1|max:5',
            'feedback'    => 'nullable|string|max:500',
        ]);

        $rating = Rating::create([
            'customer_id' => $request->customer_id,
            'vehicle_id'  => $request->vehicle_id,
            'rating'      => $request->rating,
            'feedback'    => $request->feedback,
            'status'      => 'pending', // 默认状态
        ]);

        return response()->json([
            'message' => 'Rating submitted successfully',
            'rating' => $rating
        ], 201);
    }

    // 用户提交评分+评论
    // public function store(Request $request, $vehicleId) {
    //     $request->validate([
    //         'customer_id' => 'required|exists:customers,id',
    //         'rating' => 'required|integer|min:1|max:5',
    //         'feedback' => 'nullable|string|max:500',
    //     ]);

    //     return response()->json(
    //         $this->ratingService->addRating(
    //             $request->customer_id,
    //             $vehicleId,
    //             $request->rating,
    //             $request->feedback
    //         ),
    //         201
    //     );
    // }

    // 获取平均分
    public function rating($vehicleId) {
        $vehicle = Vehicle::findOrFail($vehicleId);
        return response()->json([
            'vehicle_id' => $vehicleId,
            'average_rating' => $vehicle->average_rating,
        ]);
    }
}


// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Services\RatingService;
// use App\Models\Vehicle;
// use Illuminate\Http\Request;

// class ReviewApiController extends Controller {
//     protected $ratingService;

//     public function __construct(RatingService $ratingService) {
//         $this->ratingService = $ratingService;
//     }

//     /**
//      * 提交评分 + 可选评论
//      */
//     public function store(Request $request, $vehicleId) {
//         $request->validate([
//             'score'   => 'required|integer|min:1|max:5',
//             'feedback' => 'nullable|string|max:500'
//         ]);

//         // 获取当前登录用户
//         $customerId = auth()->id();
//         if (!$customerId) {
//             return response()->json(['error' => 'Unauthorized'], 401);
//         }

//         // 检查是否已评分
//         if ($this->ratingService->hasRated($customerId, $vehicleId)) {
//             return response()->json([
//                 'message' => 'You have already rated this vehicle.'
//             ], 400);
//         }

//         // 保存评分（包含可选 feedback）
//         $rating = $this->ratingService->addRating(
//             $vehicleId,
//             $customerId,
//             $request->score,
//             $request->feedback
//         );

//         return response()->json([
//             'message' => 'Review submitted successfully',
//             'rating'  => $rating
//         ], 201);
//     }

//     /**
//      * 获取车辆所有评分
//      */
//     public function index($vehicleId) {
//         $vehicle = Vehicle::with(['ratings'])->findOrFail($vehicleId);

//         return response()->json([
//             'vehicle_id'      => $vehicle->id,
//             'average_rating'  => $vehicle->average_rating,
//             'ratings'         => $vehicle->ratings,
//         ], 200);
//     }

//     /**
//      * 检查用户是否已对该车评分
//      */
//     public function hasRated($vehicleId) {
//         $customerId = auth()->id();
//         if (!$customerId) {
//             return response()->json(['error' => 'Unauthorized'], 401);
//         }

//         return $this->ratingService->hasRatedJson($customerId, $vehicleId);
//     }
// }
