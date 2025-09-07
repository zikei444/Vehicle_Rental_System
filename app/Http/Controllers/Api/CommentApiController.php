<?php
// app/Http/Controllers/Api/CommentApiController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentApiController extends Controller {
    protected $commentService;

    public function __construct(CommentService $commentService) {
        $this->commentService = $commentService;
    }

    public function index($vehicleId) {
        return response()->json($this->commentService->getComments($vehicleId));
    }

    public function store(Request $request, $vehicleId) {
        $request->validate(['content' => 'required|string|max:500']);
        return response()->json(
            $this->commentService->addComment($vehicleId, $request->content),
            201
        );
    }
}
