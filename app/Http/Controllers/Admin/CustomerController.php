<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
     public function index(Request $request)
    {

         $customers = Customer::query()
        ->withCount([
            'visitationSchedules',
            'visitationSchedules as published_visitation_schedules_count' => function ($query) {
                $query->published();
            },
        ])
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        })
        ->latest()
        ->paginate(20)
        ->withQueryString();
        
    return view(
        'admin.customers.index',
        compact('customers')
    );
    }

    public function show(Customer $customer)
    {
        return view(
            'admin.customers.show',
            compact('customer')
        );
    }

    public function toggle(Customer $customer)
    {
        $customer->update([
            'is_active' => !$customer->is_active
        ]);

        return back();
    }

    public function changeRole(Request $request, Customer $customer)
    {
        $request->validate([
            'role' => 'required'
        ]);

        $customer->update([
            'role' => $request->role
        ]);

        return back();
    }
}
