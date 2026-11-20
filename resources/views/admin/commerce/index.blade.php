@extends('twill::layouts.free')
@push('extra_css')
<link rel="stylesheet" href="{{ asset('css/commerce.css') }}?v=3">
@endpush
@section('customPageContent')
@php
    $sections = ['orders'=>'Đơn hàng','deposits'=>'Tiền lưu ký','transactions'=>'Giao dịch ngân hàng','ledger'=>'Sổ lưu ký','categories'=>'Danh mục mặt hàng','products'=>'Mặt hàng'];
    $statuses = ['AWAITING_PAYMENT'=>'Chờ thanh toán','PENDING'=>'Chờ thanh toán','PAID'=>'Đã nhận tiền','PAYMENT_RECEIVED'=>'Đã nhận tiền','PREPARING'=>'Đang chuẩn bị','DELIVERED'=>'Đã giao hàng','CREDITED'=>'Đã ghi có','MATCHED'=>'Đã đối soát','REVIEW'=>'Cần đối soát'];
@endphp
<div class="commerce-page">
<span class="commerce-eyebrow">QUẢN LÝ GIAO DỊCH</span>
<h1>Đơn hàng và lưu ký</h1><p class="commerce-subtitle">Theo dõi đơn hàng, tiền gửi và các giao dịch thanh toán.</p>
<nav class="commerce-tabs" aria-label="Quản lý đơn hàng và lưu ký">
@foreach($sections as $key=>$label)
<a href="{{ route('admin.commerce.index', ['section'=>$key]) }}" @if($section === $key) aria-current="page" @endif>{{ $label }}</a>
@endforeach
</nav>
@if(session('success'))<p class="commerce-alert commerce-alert--success" role="status">{{ session('success') }}</p>@endif
@foreach($errors->all() as $error)<p class="commerce-alert" role="alert">{{ $error }}</p>@endforeach
@if($section === 'categories')
<div class="commerce-panel"><h2>Thêm danh mục mặt hàng</h2>
<form class="commerce-form" method="POST" action="{{ route('admin.commerce.category') }}">
@csrf
<label>Tên danh mục <input name="name" required maxlength="100" placeholder="Đồ uống, Đồ ăn..."></label>
<label>Thứ tự <input name="position" type="number" min="0" max="100000" value="0" required></label>
<input type="hidden" name="is_active" value="1"><button type="submit">Thêm</button>
</form></div>
@elseif($section === 'products')
<div class="commerce-panel"><h2>Thêm mặt hàng</h2>
<form class="commerce-form" method="POST" enctype="multipart/form-data" action="{{ route('admin.commerce.product') }}">
@csrf
<label>Tên <input name="name" required maxlength="255"></label>
<label>Mô tả <input name="description"></label>
<label>Danh mục <select name="category_id" required><option value="">Chọn danh mục</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}{{ $category->is_active ? '' : ' (đã ẩn)' }}</option>@endforeach</select></label>
<label>Ảnh mặt hàng <input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
<label>Giá VNĐ <input name="price" type="number" min="1" max="100000000" required></label>
<input type="hidden" name="is_active" value="1"><button type="submit">Thêm</button>
</form></div>
@elseif(in_array($section, ['orders','deposits','transactions']))
<div class="commerce-panel"><form class="commerce-form" method="GET"><input type="hidden" name="section" value="{{ $section }}"><label>Trạng thái<select name="status"><option value="">Tất cả trạng thái</option>@foreach(($section === "orders" ? ["AWAITING_PAYMENT", "PAYMENT_RECEIVED", "PREPARING", "DELIVERED"] : ($section === "deposits" ? ["AWAITING_PAYMENT", "CREDITED"] : ["MATCHED", "REVIEW"])) as $status)<option value="{{ $status }}" @selected(request("status") === $status)>{{ $statuses[$status] }}</option>@endforeach</select></label><button type="submit">Lọc kết quả</button><a class="commerce-reset" href="{{ route('admin.commerce.index',['section'=>$section]) }}">Xóa lọc</a></form></div>
@endif
<div class="commerce-summary"><h2>{{ $sections[$section] }}</h2><span class="commerce-count">{{ number_format($records->total(), 0, ",", ".") }} bản ghi</span></div>
<div class="commerce-table"><table>
<thead><tr><th>Mã</th><th>Thời gian</th><th>Thông tin</th><th>{{ $section === 'categories' ? 'Số mặt hàng' : 'Số tiền (VNĐ)' }}</th><th>Trạng thái / thao tác</th></tr></thead>
<tbody>
@forelse($records as $record)
<tr><td>#{{ $record->id }}</td><td class="commerce-date">{{ $record->created_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</td>
<td class="commerce-detail">
@if($section === 'categories')
<form class="commerce-edit" method="POST" action="{{ route('admin.commerce.category') }}">@csrf
<input type="hidden" name="id" value="{{ $record->id }}">
<label>Tên danh mục <input name="name" value="{{ $record->name }}" maxlength="100" required></label>
<label>Thứ tự <input name="position" type="number" min="0" max="100000" value="{{ $record->position }}" required></label>
<select name="is_active" aria-label="Trạng thái danh mục"><option value="1" @selected($record->is_active)>Hiển thị</option><option value="0" @selected(!$record->is_active)>Ẩn</option></select><button>Lưu</button>
</form>
@elseif($section === 'products')
<form class="commerce-edit" method="POST" enctype="multipart/form-data" action="{{ route('admin.commerce.product') }}" id="product-{{ $record->id }}">@csrf
<input type="hidden" name="id" value="{{ $record->id }}">
<label>Tên <input name="name" value="{{ $record->name }}" required></label>
<label>Mô tả <input name="description" value="{{ $record->description }}"></label>
<label>Danh mục <select name="category_id" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected($record->category_id === $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (đã ẩn)' }}</option>@endforeach</select></label>
<label>Thay ảnh <input name="image" type="file" accept="image/jpeg,image/png,image/webp"></label>
@if($record->image_url)<img class="commerce-product-image" src="{{ $record->image_url }}" alt="Ảnh {{ $record->name }}" loading="lazy">@endif
<label>Giá <input name="price" type="number" min="1" max="100000000" value="{{ $record->price }}" required></label>
<select name="is_active" aria-label="Trạng thái bán"><option value="1" @selected($record->is_active)>Đang bán</option><option value="0" @selected(!$record->is_active)>Ngừng bán</option></select><button>Lưu</button>
</form>
@elseif($section === 'transactions')
SePay #{{ $record->sepay_id }} · Thanh toán #{{ $record->payment_id ?? '—' }}<br>
{{ $record->payload['accountNumber'] ?? '' }} · {{ $record->payload['content'] ?? '' }}<br>Lý do: {{ $record->reason ?? '—' }}
@if($record->reconciled_at)
<br>Ghép thủ công bởi quản trị viên #{{ $record->reconciled_by }} lúc {{ $record->reconciled_at->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}: {{ $record->reconciliation_note }}
@endif
@else
Phạm nhân #{{ $record->prisoner_id }}
@if($section !== 'ledger') · Thân nhân #{{ $record->customer_id }}<br>Mã chuyển khoản: {{ $record->payment?->code }}<br>Thanh toán: <span class="commerce-status commerce-status--{{ strtolower($record->payment?->status ?? "pending") }}">{{ $statuses[$record->payment?->status] ?? "Chưa có" }}</span>@endif
@if(in_array($section, ['orders', 'deposits']) && $record->sender_details)
<p><strong>Người gửi:</strong> {{ $record->sender_details['name'] }} · CCCD/CMND {{ $record->sender_details['identity_number'] }} · {{ $record->sender_details['phone'] }} · sinh {{ $record->sender_details['birth_year'] }} · {{ $record->sender_details['address'] }}</p>
<p><strong>Phạm nhân nhận:</strong> {{ $record->recipient_details['name'] }} · số giam {{ $record->recipient_details['prisoner_code'] }} · sinh {{ $record->recipient_details['birth_year'] }} · {{ $record->recipient_details['gender'] }} · {{ $record->recipient_details['address'] }} · {{ $record->recipient_details['group'] }} · {{ $record->recipient_details['subcamp'] }}</p>
@endif
@if($section === 'orders')
@foreach($record->items as $item)<p>{{ $item->name }} × {{ $item->quantity }} · {{ number_format($item->price) }} VNĐ</p>@endforeach
<p>{{ $record->note }}</p>
@elseif($section === 'ledger')<br>Yêu cầu lưu ký #{{ $record->deposit_request_id }} · Giao dịch #{{ $record->bank_transaction_id }}@endif
@endif
</td><td class="commerce-amount">{{ number_format($section === 'categories' ? $record->products_count : ($record->total ?? $record->amount ?? $record->price), 0, ',', '.') }}</td>
<td><span class="commerce-status commerce-status--{{ strtolower($record->status ?? ($section === "ledger" ? "credited" : ($record->is_active ? "active" : "inactive"))) }}">{{ $statuses[$record->status] ?? ($section === "ledger" ? "Đã ghi có" : ($record->is_active ? ($section === 'categories' ? 'Hiển thị' : 'Đang bán') : ($section === 'categories' ? 'Ẩn' : 'Ngừng bán'))) }}</span>
@if($section === 'transactions' && $record->status === 'REVIEW' && $record->reason === 'UNKNOWN_PAYMENT')
<form class="commerce-action commerce-reconcile" method="POST" action="{{ route('admin.commerce.transaction.reconcile', $record->id) }}">@csrf
<label>Mã chuyển khoản trên đơn hàng <input name="payment_code" type="text" maxlength="22" required placeholder="DH..."></label>
<label>Ghi chú đối soát <input name="note" maxlength="500" minlength="10" required placeholder="Đã kiểm tra sao kê và người chuyển"></label>
<label class="commerce-confirmation"><input name="confirmed" type="checkbox" value="1" required> Đã đối chiếu sao kê và xác nhận đúng đơn</label>
<button>Ghi nhận thanh toán</button></form>
@endif
@if($section === 'orders' && in_array($record->status, ['PAYMENT_RECEIVED','PAID','PREPARING']))
@if(in_array($record->status, ['PAYMENT_RECEIVED','PAID']))
<form class="commerce-action" method="POST" action="{{ route('admin.commerce.order', $record->id) }}">@csrf
<input type="hidden" name="status" value="PREPARING">
<button>Chuẩn bị hàng</button></form>
@endif
<form class="commerce-action" method="POST" action="{{ route('admin.commerce.order', $record->id) }}">@csrf
<input type="hidden" name="status" value="DELIVERED">
<button>Xác nhận đã giao hàng</button></form>
@endif
</td></tr>
@empty<tr><td class="commerce-empty" colspan="5">Chưa có dữ liệu trong mục này.</td></tr>@endforelse
</tbody></table></div>
<div class="commerce-pagination"><span>Trang {{ $records->currentPage() }} / {{ $records->lastPage() }}</span>@if($records->previousPageUrl())<a href="{{ $records->previousPageUrl() }}">← Trang trước</a>@endif @if($records->nextPageUrl())<a href="{{ $records->nextPageUrl() }}">Trang sau →</a>@endif</div>
<p class="commerce-note">Các giao dịch “Cần đối soát” được giữ lại để kiểm tra trước khi ghi nhận tiền.</p>
</div>
@endsection
