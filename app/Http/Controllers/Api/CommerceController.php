<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\CommerceService;
use Illuminate\Http\Request;

class CommerceController extends Controller
{
    public function products()
    {
        return response()->json(Product::where('is_active', true)->orderBy('name')->paginate(30));
    }

    public function createOrder(Request $request, CommerceService $service)
    {
        $data = $request->validate([
            'prisoner_id' => 'required|integer|min:1', 'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:100',
            'items.*.product_id' => 'required|integer|min:1|distinct',
            'items.*.quantity' => 'required|integer|min:1|max:100',
        ]);
        $payment = $service->create($request->user(), 'order', $data, $this->key($request));

        return response()->json(['data' => $payment->order->load('items'), 'payment' => $service->paymentData($payment)], 201);
    }

    public function createDeposit(Request $request, CommerceService $service)
    {
        $data = $request->validate(['prisoner_id' => 'required|integer|min:1', 'amount' => 'required|integer|min:1000|max:100000000']);
        $payment = $service->create($request->user(), 'deposit', $data, $this->key($request));

        return response()->json(['data' => $payment->depositRequest, 'payment' => $service->paymentData($payment)], 201);
    }

    private function key(Request $request): string
    {
        return validator(['key' => $request->header('Idempotency-Key')], ['key' => 'required|string|max:100|regex:/^[A-Za-z0-9_-]+$/'])->validate()['key'];
    }

    public function orders(Request $request)
    {
        return response()->json(Order::where('customer_id', $request->user()->id)->with('items', 'payment')->latest()->paginate(20));
    }

    public function order(Request $request, int $id)
    {
        return response()->json(['data' => Order::where('customer_id', $request->user()->id)->with('items', 'payment')->findOrFail($id)]);
    }

    public function deposits(Request $request)
    {
        return response()->json(DepositRequest::where('customer_id', $request->user()->id)->with('payment')->latest()->paginate(20));
    }

    public function deposit(Request $request, int $id)
    {
        return response()->json(['data' => DepositRequest::where('customer_id', $request->user()->id)->with('payment')->findOrFail($id)]);
    }

    public function payment(Request $request, int $id, CommerceService $service)
    {
        return response()->json(['data' => $service->paymentData(Payment::where('customer_id', $request->user()->id)->findOrFail($id))]);
    }
}
