<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;

class AdminRatingController extends Controller
{
    // 显示所有评论（不管 pending/approved/rejected）
    public function index()
    {
        $ratings = Rating::with(['customer', 'vehicle'])->get();
        return view('ratings_admin.index', compact('ratings'));
    }

    // 批准评论
    public function approve($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->update(['status' => 'approved']);

        return redirect()->back()->with('success', '评论已批准');
    }

    // 拒绝评论
    public function reject($id)
    {
        $rating = Rating::findOrFail($id);
        $rating->update(['status' => 'rejected']);

        return redirect()->back()->with('success', '评论已拒绝');
    }
}
