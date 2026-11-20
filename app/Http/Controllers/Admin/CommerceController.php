<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankTransaction;
use App\Models\DepositRequest;
use App\Models\Order;
use App\Models\PrisonerLedgerEntry;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\CommerceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommerceController extends Controller
{
    public function index(Request $request)
    {
        $section = $request->query('section', 'orders');
        abort_unless(in_array($section, ['orders', 'deposits', 'transactions', 'ledger', 'categories', 'products']), 404);
        $query = match ($section) {
            'orders' => Order::with('items', 'payment'),
            'deposits' => DepositRequest::with('payment'),
            'transactions' => BankTransaction::query(),
            'ledger' => PrisonerLedgerEntry::query(),
            'categories' => ProductCategory::withCount('products'),
            'products' => Product::with('productCategory'),
        };
        if ($request->filled('status') && in_array($section, ['orders', 'deposits', 'transactions'])) {
            $query->where('status', $request->string('status')->toString());
        }

        return view('admin.commerce.index', [
            'section' => $section,
            'records' => $query->latest()->paginate(30)->withQueryString(),
            'categories' => ProductCategory::orderBy('position')->orderBy('name')->get(),
        ]);
    }

    public function category(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer|exists:product_categories,id',
            'name' => ['required', 'string', 'max:100', Rule::unique('product_categories', 'name')->ignore($request->input('id'))],
            'position' => 'required|integer|min:0|max:100000',
            'is_active' => 'required|boolean',
        ]);
        $category = isset($data['id']) ? ProductCategory::findOrFail($data['id']) : new ProductCategory;
        unset($data['id']);
        $category->fill($data)->save();

        return back()->with('success', 'Đã lưu danh mục mặt hàng.');
    }

    public function product(Request $request)
    {
        $data = $request->validate([
            'id' => 'nullable|integer|exists:products,id', 'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000', 'price' => 'required|integer|min:1|max:100000000',
            'category_id' => 'required|integer|exists:product_categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,webp|max:5120',
            'is_active' => 'required|boolean',
        ]);
        $product = isset($data['id']) ? Product::findOrFail($data['id']) : new Product;
        unset($data['id']);
        $image = $data['image'] ?? null;
        unset($data['image']);
        $oldPath = $product->image_path;
        $newPath = null;

        if ($image) {
            $newPath = $image->store('products', 'public');
            if (! $newPath) {
                throw ValidationException::withMessages(['image' => 'Không thể tải ảnh lên. Vui lòng thử lại.']);
            }
            $data['image_path'] = $newPath;
            $data['image_url'] = null;
        }

        try {
            $product->fill($data)->save();
        } catch (\Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }
        if ($newPath && $oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('success', 'Đã lưu mặt hàng.');
    }

    public function order(Request $request, int $id)
    {
        $data = $request->validate(['status' => 'required|in:PREPARING,DELIVERED']);
        DB::transaction(function () use ($id, $data) {
            $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
            $allowedFrom = $data['status'] === 'PREPARING'
                ? ['PAYMENT_RECEIVED', 'PAID']
                : ['PAYMENT_RECEIVED', 'PAID', 'PREPARING'];
            if (! in_array($order->status, $allowedFrom, true) || $order->payment?->status !== 'PAID') {
                throw ValidationException::withMessages(['status' => 'Đơn chưa nhận tiền hoặc trạng thái không hợp lệ.']);
            }
            $order->update($data);
        });

        return back()->with('success', 'Đã cập nhật đơn hàng.');
    }

    public function reconcileTransaction(Request $request, int $id, CommerceService $service)
    {
        $data = $request->validate([
            'order_id' => 'required_without:payment_code|prohibits:payment_code|integer|min:1',
            'payment_code' => ['required_without:order_id', 'prohibits:order_id', 'string', 'regex:/^DH[A-Z0-9]{6,20}$/i'],
            'note' => 'required|string|min:10|max:500',
            'confirmed' => 'accepted',
        ]);
        $service->reconcileOrder($id, $data['payment_code'] ?? $data['order_id'], $request->user('twill_users')->id, $data['note']);

        return back()->with('success', 'Đã ghép giao dịch với đơn hàng và ghi nhận thanh toán.');
    }
}
