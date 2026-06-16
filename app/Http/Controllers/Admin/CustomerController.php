<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
     public function index()
    {
        $customers = Customer::query()
            ->latest()
            ->paginate(20);

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
}
