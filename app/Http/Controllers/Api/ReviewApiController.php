<?php

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

        // 获取当前用户 ID（假设用 Laravel Auth）
        $customerId = auth()->id(); 
        if (!$customerId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 检查是否已评分
        if ($this->ratingService->hasRated($customerId, $vehicleId)) {
            return response()->json([
                'message' => 'You have already rated this vehicle.'
            ], 400);
        }

        // 保存评分
        $rating = $this->ratingService->addRating(
            $vehicleId,
            $customerId,
            $request->score,
            $request->content
        );

        // 如果有评论，另外保存
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
     * 获取车辆所有评分和评论
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

    /**
     * 检查用户是否已对该车评分
     */
    public function hasRated($vehicleId) {
        $customerId = auth()->id(); 
        if (!$customerId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->ratingService->hasRatedJson($customerId, $vehicleId);
    }
}
