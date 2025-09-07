<?php

namespace App\Observers;

use App\Models\Rating;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RatingObserver
{
    /**
     * 当有新评分创建时
     */
    public function created(Rating $rating): void
    {
        $this->updateVehicleAverage($rating);

        // 如果有评论，通知管理员
        if (!empty($rating->feedback)) {
            $this->notifyAdmin($rating);
        }
    }

    /**
     * 当评分被更新时
     */
    public function updated(Rating $rating): void
    {
        $this->updateVehicleAverage($rating);

        if (!empty($rating->feedback)) {
            $this->notifyAdmin($rating);
        }
    }

    /**
     * 当评分被删除时
     */
    public function deleted(Rating $rating): void
    {
        $this->updateVehicleAverage($rating);
    }

    /**
     * 更新车辆平均评分
     */
    private function updateVehicleAverage(Rating $rating): void
    {
        $vehicle = $rating->vehicle;

        if ($vehicle) {
            $avg = $vehicle->ratings()
                ->approved() // 只统计已批准的评分
                ->avg('rating');

            $vehicle->update([
                'average_rating' => $avg ?? 0
            ]);
        }
    }

    /**
     * 通知管理员
     */
    private function notifyAdmin(Rating $rating): void
    {
        // 方式 1: 写日志 (最简单)
        Log::info("新评分通知: 车辆ID {$rating->vehicle_id}, 用户ID {$rating->customer_id}, 分数 {$rating->rating}, 评论: {$rating->feedback}");

        // 方式 2: 发邮件 (如果你有 mail 配置)
        /*
        Mail::raw("用户 {$rating->customer_id} 给车辆 {$rating->vehicle_id} 评分 {$rating->rating}, 评论: {$rating->feedback}", function ($message) {
            $message->to('admin@example.com')
                    ->subject('新评分与评论通知');
        });
        */
    }
}
