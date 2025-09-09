<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserApiController extends Controller
{
    /**
     * get customer id
     */
    public function getCustomerId(Request $request): JsonResponse
    {
        $userId = $request->user()->id ?? null;

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated'
            ], 401);
        }

        $customer = Customer::where('user_id', $userId)->first();

        if (!$customer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'customer_id' => $customer->id
        ]);
    }
}
