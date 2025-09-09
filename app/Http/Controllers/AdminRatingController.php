<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class AdminRatingController extends Controller
{
    // 显示所有评论（不管 pending/approved/rejected）
    public function index()
    {
        $ratings = Rating::with(['customer', 'reservation'])->get();
        return view('ratings_admin.index', compact('ratings'));
    }

    // // 批准评论
    // public function approve($id)
    // {
    //     $rating = Rating::findOrFail($id);
    //     $rating->update(['status' => 'approved']);

    //     return redirect()->back()->with('success', '评论已批准');
    // }
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
        $request->validate([
            'reply' => 'required|string|max:500',
        ]);

        $rating->adminreply = $request->reply;
        $rating->save();

    return response()->json(['success'=>true]);
    }
    // 拒绝评论
    public function reject($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->update(['status' => 'rejected']);

        return redirect()->back()->with('success', '评论已拒绝');
    }
}
