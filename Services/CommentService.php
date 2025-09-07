<?php

namespace App\Services;

use App\Models\Comment;

class CommentService {
    // 获取某个车辆的所有评论
    public function getComments($vehicleId) {
        return Comment::where('vehicle_id', $vehicleId)->get();
    }

    // 添加评论（会触发 Observer）
    public function addComment($vehicleId, $content) {
        return Comment::create([
            'vehicle_id' => $vehicleId,
            'content' => $content,
        ]);
    }
}
