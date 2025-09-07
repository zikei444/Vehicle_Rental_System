<?php
// app/Observers/CommentObserver.php
namespace App\Observers;

use App\Models\Comment;
use Illuminate\Support\Facades\Log;

class CommentObserver {
    public function created(Comment $comment) {
        Log::info("新评论: {$comment->content} (Vehicle ID: {$comment->vehicle_id})");
        // 这里可以拓展：发邮件通知管理员 / 推送消息
    }
}
