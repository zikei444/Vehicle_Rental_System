<?php
// STUDENT NAME: Kek Xin Ying
// STUDENT ID: 23WMR14547

namespace App\Observers;

use App\Models\User;
use App\Models\Rating;
use App\Models\RatingLog;
use App\Notifications\RatingReplied;
use App\Notifications\NewRatingCreated;

class RatingObserver
{

    // When a new rating is created → notify admins
// When a new rating is created → notify admins
    public function created(Rating $rating)
    {
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            $exists = $admin->notifications()
                ->where('type', 'App\Notifications\NewRatingCreated')
                ->whereJsonContains('data->rating_id', $rating->id)
                ->exists();

            if (!$exists) {
                $admin->notify(new NewRatingCreated($rating));
            }
        }
    }


    // When rating is updated → notify customer if admin replied
    public function updated(Rating $rating)
{
    // Only trigger if admin replied and reply is not empty
    if ($rating->wasChanged('adminreply') && $rating->adminreply) {

        // Save to rating log
        RatingLog::create([
            'rating_id' => $rating->id,
            'customer_id' => $rating->customer_id,
            'reply' => $rating->adminreply,
        ]);

        // Notify customer safely
        $customer = $rating->customer;

        if ($customer) {
            $user = $customer->user;

            // Only send notification if user exists and hasn't received one for this reply yet
            if ($user && !$user->notifications()
                ->where('type', RatingReplied::class)
                ->whereJsonContains('data->rating_id', $rating->id)
                ->exists()
            ) {
                $user->notify(new RatingReplied($rating, $rating->adminreply));
            }
        }
    }// ✅ 每次评分更新后，更新车辆平均分和评论数
    $this->updateVehicleStats($rating);
    }
    // 当评分创建/修改/删除时，更新车辆平均分和评论数
    private function updateVehicleStats(Rating $rating)
    {
        $vehicle = $rating->vehicle;

        if ($vehicle) {
            $approvedRatings = $vehicle->ratings()->approved();

            $vehicle->average_rating = $approvedRatings->avg('rating') ?? 0;
//            $vehicle->total_reviews  = $approvedRatings->count();

            $vehicle->save();
        }
    }
    public function deleted(Rating $rating)
    {
        // Update vehicle stats after a rating is deleted
        $this->updateVehicleStats($rating);
    }
}
