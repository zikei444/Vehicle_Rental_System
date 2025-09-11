<?php
namespace App\Observers;
use App\Models\User;
use App\Models\Rating;
use App\Models\RatingLog;
use App\Notifications\RatingReplied;
use Illuminate\Support\Facades\Log;
use App\Notifications\NewRatingCreated;


class RatingObserver
{
    // 当新的 Rating 被创建
    public function created(Rating $rating)
    {
        // 通知所有管理员
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewRatingCreated($rating));
        }

        // // 反应3：更新车辆平均分
        // $vehicle = $rating->vehicle;
        // if ($vehicle) {
        //     $vehicle->avg_rating = $vehicle->ratings()->avg('rating');
        //     $vehicle->save();
        // }
    }
    /**
     * Handle the Rating "updated" event.
     */
 // 当 Rating 被更新时触发
    public function updated(Rating $rating)
    {
        // 如果有 admin reply
        if ($rating->wasChanged('adminreply') && $rating->adminreply) {
            // 1️⃣ 存入日志
            RatingLog::create([
                'rating_id' => $rating->id,
                'customer_id' => $rating->customer_id,
                'reply' => $rating->adminreply,
            ]);

            // 2️⃣ 发通知
            if ($rating->customer && $rating->customer->user) {
                $rating->customer->user->notify(new RatingReplied($rating));
            }
        }
    }
}
