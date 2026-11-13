<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\DepositRequest;
use App\Models\Order;
use App\Models\PrisonerLedgerEntry;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommerceController extends Controller
{
    public function index(Request $request)
    {
        $section = $request->query('section', 'orders');
        abort_unless(in_array($section, ['orders', 'deposits', 'transactions', 'ledger', 'products']), 404);
        $query = match ($section) {
            'orders' => Order::with('items', 'payment'),
            'deposits' => DepositRequest::with('payment'),
            'transactions' => BankTransaction::query(),
            'ledger' => PrisonerLedgerEntry::query(),
            'products' => Product::query(),
        };
        if ($request->filled('status') && in_array($section, ['orders', 'deposits', 'transactions'])) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('admin.commerce.index', ['section' => $section, 'records' => $query->latest()->paginate(30)->withQueryString()]);
    }

    public function product(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer|exists:products,id', 'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000', 'price' => 'required|integer|min:1|max:100000000',
            'is_active' => 'required|boolean',
        ]);
        $product = isset($data['id']) ? Product::findOrFail($data['id']) : new Product;
        unset($data['id']);
        $product->fill($data)->save();

        return back()->with('success', 'Đã lưu mặt hàng.');
    }

    public function order(Request $request, int $id)
    {
        $data = $request->validate(['status' => 'required|in:PREPARING,DELIVERED']);
        DB::transaction(function () use ($id, $data) {
            $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
            $expected = $data['status'] === 'PREPARING' ? 'PAID' : 'PREPARING';
            abort_unless($order->status === $expected && $order->payment?->status === 'PAID', 409, 'Đơn chưa thanh toán hoặc trạng thái không hợp lệ.');
            $order->update($data);
        });

        return back()->with('success', 'Đã cập nhật đơn hàng.');
    }
}
