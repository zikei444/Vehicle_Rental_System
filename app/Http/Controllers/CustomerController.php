<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\Factories\UserFactory;

class CustomerController extends Controller
{
    // Display all customers
    public function index(Request $request)
    {
        // Retrieve all customers using ORM
        $customers = User::where('role', 'customer')
            ->with('customer') // eager load customer details like phone
            ->get();

        // Format data for blade
        $customers = $customers->map(function ($user) {
            return [
                'user_id'     => $user->id,
                'customer_id' => $user->customer->id ?? '-',
                'name'        => $user->name,
                'email'       => $user->email,
                'phoneNo'     => $user->customer->phoneNo ?? '-',
                'role'        => $user->role,
                'created_at'  => $user->created_at->format('Y-m-d H:i:s'),   // include time
                'updated_at'  => $user->updated_at ? $user->updated_at->format('Y-m-d H:i:s') : null, // include time
            ];
        });

        $editId = $request->query('edit_id', null);

        return view('user.customerManagement', compact('customers', 'editId'));
    }

    // Update customer
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'phone'    => 'required|string|max:20',
        ]);

        $user = User::findOrFail($id);
        $user->name = $validated['username'];
        $user->save(); // updated_at is automatically refreshed

        if ($user->customer) {
            $user->customer->update(['phoneNo' => $validated['phone']]);
        }

        return redirect()->route('admin.customerManagement')->with('message', 'Customer updated successfully.');
    }


}
