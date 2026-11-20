<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\CommerceService;
use App\Enums\ToPhamNhanEnum;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommerceController extends Controller
{
    private const ORDER_RELATIONS = ['items', 'payment', 'customer:id,name,phone', 'prisoner:id,username,prisoner_code'];

    public function categories()
    {
        return response()->json(['data' => ProductCategory::where('is_active', true)
            ->orderBy('position')->orderBy('name')->get(['id', 'name'])]);
    }

    public function products()
    {
        $filters = request()->validate([
            'category_id' => 'sometimes|integer|min:1',
            'category' => 'sometimes|string|max:100',
            'search' => 'sometimes|string|max:100',
        ]);

        return response()->json(Product::with('productCategory:id,name')->where('is_active', true)
            ->whereHas('productCategory', fn ($query) => $query->where('is_active', true))
            ->when($filters['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($filters['category'] ?? null, fn ($query, $name) => $query->whereHas('productCategory', fn ($category) => $category->where('name', $name)))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', '%'.$search.'%'))
            ->orderBy('name')->paginate(30));
    }

    public function createOrder(Request $request, CommerceService $service)
    {
        $data = $request->validate($this->personRules() + [
            'key' => ['sometimes', 'string', 'regex:/^DH[A-Z0-9]{6,20}$/i'],
            'note' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:100',
            'items.*.product_id' => 'required|integer|min:1|distinct',
            'items.*.quantity' => 'required|integer|min:1|max:100',
        ]);
        if (isset($data['key'])) {
            $data['key'] = strtoupper($data['key']);
        }
        $payment = $service->create($request->user(), 'order', $data, $this->key($request));

        return response()->json(['data' => $this->orderData($payment->order->load(self::ORDER_RELATIONS), $service),
            'payment' => $service->paymentData($payment)], 201);
    }

    public function createDeposit(Request $request, CommerceService $service)
    {
        $data = $request->validate($this->personRules() + ['amount' => 'required|integer|min:1000|max:100000000']);
        $payment = $service->create($request->user(), 'deposit', $data, $this->key($request));

        return response()->json(['data' => $payment->depositRequest, 'payment' => $service->paymentData($payment)], 201);
    }

    private function key(Request $request): string
    {
        return validator(['key' => $request->header('Idempotency-Key')], ['key' => 'required|string|max:100|regex:/^[A-Za-z0-9_-]+$/'])->validate()['key'];
    }

    private function personRules(): array
    {
        $year = now()->year;

        return [
            'sender' => 'required|array:name,identity_number,phone,birth_year,address',
            'sender.name' => 'required|string|max:255',
            'sender.identity_number' => ['required', 'regex:/^(?:[0-9]{9}|[0-9]{12})$/'],
            'sender.phone' => ['required', 'regex:/^0[0-9]{9}$/'],
            'sender.birth_year' => "required|integer|between:1900,$year",
            'sender.address' => 'required|string|max:500',
            'recipient' => 'required|array:name,prisoner_code,birth_year,gender,address,group,subcamp',
            'recipient.name' => 'required|string|max:255',
            'recipient.prisoner_code' => 'required|string|max:100',
            'recipient.birth_year' => "required|integer|between:1900,$year",
            'recipient.gender' => 'required|in:MALE,FEMALE',
            'recipient.address' => 'required|string|max:500',
            'recipient.group' => ['required', Rule::enum(ToPhamNhanEnum::class)],
            'recipient.subcamp' => 'required|string|max:255',
        ];
    }

    public function orders(Request $request, CommerceService $service)
    {
        return response()->json(Order::where('customer_id', $request->user()->id)
            ->with(self::ORDER_RELATIONS)->latest()->paginate(20)
            ->through(fn (Order $order) => $this->orderData($order, $service)));
    }

    public function order(Request $request, int $id, CommerceService $service)
    {
        $order = Order::where('customer_id', $request->user()->id)->with(self::ORDER_RELATIONS)->findOrFail($id);

        return response()->json(['data' => $this->orderData($order, $service)]);
    }

    private function orderData(Order $order, CommerceService $service): array
    {
        $data = $order->toArray();
        $data['payment'] = $order->payment ? $service->paymentData($order->payment) : null;

        return $data;
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
