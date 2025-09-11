<?php

namespace App\Http\Controllers;
use App\Models\Vehicle;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Notifications\RatingReplied;

class AdminRatingController extends Controller
{
    // 显示所有评论（不管 pending/approved/rejected）
    public function index()
    {
        $ratings = Rating::with(['customer.user', 'reservation'])->get();
        return view('ratings_admin.index', compact('ratings'));
    }
    public function dashboard()
    {
        // 获取所有车辆及其评分平均值
        $vehicles = Vehicle::with('ratings')->get()->map(function ($vehicle) {
            $vehicle->average_rating = $vehicle->ratings()
                ->approved()
                ->avg('rating') ?? 0;
            return $vehicle;
        });
            

        // // 如果要按月份统计（可选）
        // $monthlyRatings = Rating::select(
        //         DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
        //         'vehicle_id',
        //         DB::raw('AVG(rating) as avg_rating')
        //     )
        //     ->groupBy('month','vehicle_id')
        //     ->with('vehicle')
        //     ->get();

        return view('ratings_admin.dashboard', compact('vehicles'));
    }
    // 每辆车的详细评分
    public function vehicleRatingsDetails(Vehicle $vehicle)
    {
        // 使用传入的 $vehicle 对象，不要再 find
        $vehicle->load('ratings.customer.user'); // 加载关联
        return view('ratings_admin.vehicle_ratings_details', compact('vehicle'));
    }


    // // 批准评论
    public function approve(Request $request, Rating $rating)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $rating->status = $request->status;
        $rating->save();

    return response()->json(['success'=>true]);
    }
    //回复
public function reply(Request $request, Rating $rating)
{
    // 表单验证
    $request->validate([
        'reply' => 'required|string|max:500',
    ]);

    //更新 reply
    $rating->adminreply = $request->reply;
    $rating->save();

    // === Observer Pattern ===
    $subject = new RatingSubject();

    $user = User::find($rating->customer->user_id); // 取得真正的 User
    $observer = new UserObserver($user, $rating);

    $subject->attach($observer);

    $subject->notify($request->reply); // 通知用户
    // === End ===

    return back()->with('success', 'Reply sent and notification created!');
}


    // 拒绝评论
    public function reject($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->update(['status' => 'rejected']);

        return redirect()->back()->with('success', '评论已拒绝');
    }

}
