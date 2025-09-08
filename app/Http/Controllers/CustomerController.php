<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


// Admin Change Customer Profile
class CustomerController extends Controller
{
    private $userApi = 'http://127.0.0.1/Vehicle_Rental_System/public/api/userApi.php';

    // Display all customers
    public function index(Request $request)
    {
        $response = Http::asForm()->get($this->userApi, [
            'action' => 'getCustomers'
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $customers = $data['status'] === 'success' ? $data['data'] : [];
        } else {
            $customers = [];
        }

        // Pass edit_id if any (for inline editing)
        $editId = $request->query('edit_id', null);

        return view('user.customerManagement', compact('customers', 'editId'));
    }

    // Update customer
    public function update(Request $request, $id)
    {
        // Validate input
        $request->validate([
            'username' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        try {
            $response = Http::asForm()->post($this->userApi, [
                'action' => 'update',
                'id' => $id,
                'username' => $request->input('username'),
                'phone' => $request->input('phone'),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $message = $data['status'] === 'success'
                    ? 'Customer updated successfully.'
                    : ($data['message'] ?? 'Update failed.');
            } else {
                $message = 'Failed to connect to the API.';
            }
        } catch (\Exception $e) {
            $message = 'Error: ' . $e->getMessage();
        }

        return redirect()->route('admin.customerManagement')->with('message', $message);
    }
}
