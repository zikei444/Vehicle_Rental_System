<?php

namespace App\Http\Controllers;
use App\Models\Vehicle;
use App\Models\Rating;
use Illuminate\Http\Request;
use App\Notifications\RatingReplied;
use App\Models\User;
use App\Observers\UserObserver;
use App\Observers\RatingObserver;

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
    //回复
    public function reply(Request $request, Rating $rating)
    {
        $request->validate([
            'reply' => 'required|string|max:500',
        ]);

        $rating->adminreply = $request->reply;
        $rating->save(); // Observer will notify customer automatically

        return back()->with('success', 'Reply sent and notification created!');
    }

    public function destroy($id)
    {
        $rating = Rating::find($id);
        if (!$rating) {
            return response()->json(['message' => 'Rating not found'], 404);
        }

        $rating->delete();
        return response()->json(['message' => 'Rating deleted successfully']);
    }


    // 拒绝评论
    public function reject($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->update(['status' => 'rejected']);

        return redirect()->back()->with('success', '评论已拒绝');
    }

}
