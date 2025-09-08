<?php

// app/Http/Controllers/Api/ReviewApiController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RatingService;
use App\Services\CommentService;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class ReviewApiController extends Controller {
    protected $ratingService;
    protected $commentService;

    public function __construct(RatingService $ratingService, CommentService $commentService) {
        $this->ratingService = $ratingService;
        $this->commentService = $commentService;
    }

    /**
     * 提交评分 + 可选评论
     */
    public function store(Request $request, $vehicleId) {
        $request->validate([
            'score'   => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:500'
        ]);

        // 保存评分
        $rating = $this->ratingService->addRating($vehicleId, $request->score);

        // 保存评论（如果有内容）
        $comment = null;
        if (!empty($request->content)) {
            $comment = $this->commentService->addComment($vehicleId, $request->content);
        }

        return response()->json([
            'message' => 'Review submitted successfully',
            'rating'  => $rating,
            'comment' => $comment
        ], 201);
    }

    /**
     * 获取该车所有评分和评论
     */
    public function index($vehicleId) {
        $vehicle = Vehicle::with(['ratings', 'comments'])->findOrFail($vehicleId);

        return response()->json([
            'vehicle_id'      => $vehicle->id,
            'average_rating'  => $vehicle->average_rating,
            'ratings'         => $vehicle->ratings,
            'comments'        => $vehicle->comments
        ], 200);
    }
}


// class RatingApiController extends Controller
// {
//     protected $ratingService;

//     public function __construct(RatingService $ratingService) {
//         $this->ratingService = $ratingService;
//     }

//     public function index($vehicleId) {
//         return response()->json($this->ratingService->getRatings($vehicleId));
//     }

//     public function store(Request $request, $vehicleId) {
//         $request->validate([
//             'customer_id' => 'required|exists:customers,id',
//             'rating'      => 'required|integer|min:1|max:5',
//             'feedback'    => 'nullable|string|max:500',
//         ]);

//         $rating = $this->ratingService->addRating(
//             $vehicleId,
//             $request->customer_id,
//             $request->rating,
//             $request->feedback
//         );

//         return response()->json($rating, 201);
//     }

//     public function average($vehicleId) {
//         $vehicle = Vehicle::findOrFail($vehicleId);
//         return response()->json($this->ratingService->getAverageRating($vehicle));
//     }

//     // Check if customer has rated a vehicle ADDED BY ZK
//     public function hasRated($vehicleId, $customerId)
//     {
//         $hasRated = Rating::where('customer_id', $customerId)
//                           ->where('vehicle_id', $vehicleId)
//                           ->exists();

//         return response()->json([
//             'customer_id' => $customerId,
//             'vehicle_id' => $vehicleId,
//             'has_rated' => $hasRated
//         ]);
//     }
// }
