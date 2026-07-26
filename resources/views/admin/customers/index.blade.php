@extends('twill::layouts.free')


@push('extra_css')
<style>
.card {
    background: #fff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
}

.toolbar {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    justify-content: flex-end;
}

.toolbar input {
    width: 300px;
    height: 40px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 0 12px;
}

.btn {
    border: 0;
    border-radius: 8px;
    padding: 10px 16px;
    cursor: pointer;
    font-weight: 600;
}

.btn-primary {
    background: #2563eb;
    color: white;
}

.btn-success {
    background: #16a34a;
    color: white;
}

.btn-danger {
    background: #dc2626;
    color: white;
}

.table-custom {
    width: 100%;
    border-collapse: collapse;
}

.table-custom th {
    background: #f8fafc;
    text-align: left;
    padding: 14px;
    border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
}

.table-custom td {
    padding: 14px;
    border-bottom: 1px solid #e5e7eb;
}

.table-custom tr:hover {
    background: #f9fafb;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.badge-success {
    background: #dcfce7;
    color: #166534;
}

.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}

.pagination {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 15px;
    font-weight: 600;
}

.role-select {
    min-width: 140px;
    height: 36px;
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 0 10px;
}

.modal-overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.4);
    display:flex;
    justify-content:center;
    align-items:center;
}

.modal{
    background:#fff;
    width:400px;
    padding:24px;
    border-radius:10px;
}

.modal-label {
    display: block;
    margin-bottom: 12px;
    font-weight: 700;
    color: #374151;
}

.modal-input {
    width: 100%;
    height: 42px;
    padding: 0 14px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: .2s;
    box-sizing: border-box;
}

</style>

@endpush

@section('content')

<div class="wrapper container" style="padding-top:20px ;">
    <div style=" width: 100%">

        <div class="card">

            <h1 style="margin-bottom:20px; font-size: 18px; font-weight: bold">
                Quản lý user
            </h1>

            <form method="GET" class="toolbar">

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Nhập tên..."
                >

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Tìm kiếm
                </button>

            </form>
<div style="overflow: auto;">

            <table class="table-custom">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Zalo ID</th>
                        <th>Họ tên</th>
                        <th>SĐT</th>
                        <th>Tổng lịch thăm</th>
                        <th>Tổng lịch đã duyệt</th>
                        <th>TB lịch/ngày</th>
                        <th>Quyền</th>
                        <th>Trạng thái</th>
                        <th width="180">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                @foreach($customers as $customer)
                    @php
                        $average = $customer->active_days_count > 0
                            ? round($customer->total_visits_count / $customer->active_days_count, 2)
                            : 0;
                    @endphp
                    <tr>

                        <td>
                            {{ $customer->id }}
                        </td>

                        <td>
                            {{ $customer->zalo_id }}
                        </td>

                        <td>
                            {{ $customer->name }}
                        </td>

                        <td>
                            {{ $customer->phone }}
                        </td>

                        <td>
                            {{ $customer->visitation_schedules_count }}
                        </td>

                        <td>
                            {{ $customer->published_visitation_schedules_count }}
                        </td>

                        <td>
                            {{ $customer->active_days_count
    ? round($customer->visitation_schedules_count/ $customer->active_days_count, 2)
    : 0 }}
                        </td>

                         <td>
    <form
        method="POST"
        action="{{ route('admin.customers.role', $customer) }}"
    >
        @csrf

        <select
            class="role-select"
            name="role"
            onchange="this.form.submit()"
        >
            @foreach(\App\Models\Customer::ROLES as $value => $label)

                <option
                    value="{{ $value }}"
                    @selected($customer->role === $value)
                >
                    {{ $label }}
                </option>

            @endforeach
        </select>

    </form>
</td>

<td style="white-space: nowrap;">
    @if($customer->is_active)
        <span class="badge badge-success">
            Đang hoạt động
        </span>
    @else
        <span class="badge badge-danger">
            Đã khóa
        </span>
    @endif
</td>

                        <td>

                            <!-- <a
                                href="{{ route('admin.customers.show', $customer) }}"
                            >
                                View
                            </a> -->

                            <form
                                method="POST"
                                action="{{ route('admin.customers.toggle', $customer) }}"
                                style="display:flex; gap: 5px; align-items: center;"
                            >
                                @csrf

                                <button
    type="submit"
    class="btn {{ $customer->is_active ? 'btn-danger' : 'btn-success' }}"
>
    {{ $customer->is_active ? 'Khóa' : 'Mở khóa' }}
</button>
<button
    type="button"
    class="btn btn-primary"
    style=" white-space: nowrap"
    onclick="openPhoneModal({{ $customer->id }}, '{{ $customer->phone }}')"
>
    Sửa SĐT
</button>

                            </form>

                            

                        </td>

                    </tr>

                @endforeach

            </tbody>

        </table>
</div>

        @if ($customers->hasPages())
<div style="margin-top:20px; text-align: right">

    @if (!$customers->onFirstPage())
        <a href="{{ $customers->previousPageUrl() }}">← Trước</a>
    @endif

    Trang {{ $customers->currentPage() }}/{{ $customers->lastPage() }}

    @if ($customers->hasMorePages())
        <a href="{{ $customers->nextPageUrl() }}">Sau →</a>
    @endif

</div>
@endif

    </div>

</div>

<div id="phoneModal" style="display:none;" class="modal-overlay">
    <div class="modal">
        <h3 class="modal-label">Sửa số điện thoại</h3>

        <form id="phoneForm" method="POST">
            @csrf

            <input
                type="text"
                name="phone"
                id="phoneInput"
                class="modal-input"
                placeholder="Nhập số điện thoại"
            >

            <div style="margin-top:20px; display: flex; justify-content: flex-end; gap: 5px">
                <button class="btn btn-success">
                    Lưu
                </button>

                <button
                    type="button"
                    class="btn btn-danger"
                    onclick="closePhoneModal()"
                >
                    Hủy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openPhoneModal(id, phone)
{
    document.getElementById('phoneModal').style.display = 'flex';

    document.getElementById('phoneInput').value = phone;

    document.getElementById('phoneForm').action =
        '/admin/customers/' + id + '/phone';
}

function closePhoneModal()
{
    document.getElementById('phoneModal').style.display = 'none';
}
</script>

@endsection

