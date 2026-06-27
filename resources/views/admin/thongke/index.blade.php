@extends('twill::layouts.free')

@section('content')



@push('extra_css')
<style>
.dashboard-filter {
    padding-top: 20px;
    padding-bottom: 20px;
    display: flex;
    gap: 16px;
    align-items: end;
    justify-content: end;
    margin-bottom: 10px;
}

.dashboard-filter .form-group {
    display: flex;
    flex-direction: column;
}

.dashboard-filter label {
    font-size: 13px;
    color: #666;
    margin-bottom: 6px;
    font-weight: 500;
}

.dashboard-filter input[type="date"] {
    height: 42px;
    padding: 0 12px;
    border: 1px solid #dcdfe6;
    border-radius: 10px;
    outline: none;
}

.dashboard-filter input[type="date"]:focus {
    border-color: #3b82f6;
}

.btn-filter {
    height: 42px;
    padding: 0 18px;
    border: none;
    border-radius: 10px;
    background: #2563eb;
    color: #fff;
    cursor: pointer;
    font-weight: 600;
}

.btn-filter:hover {
    opacity: .9;
}

.btn-reset {
    height: 42px;
    padding: 0 18px;
    border-radius: 10px;
    text-decoration: none;
    background: #e5e7eb;
    color: #374151;
    display: flex;
    align-items: center;
    font-weight: 600;
}


.dashboard-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:20px;
    padding:10px 30px;
}

.dashboard-card{
    background:#fff;
    border-radius:12px;
    padding:20px;
    box-shadow:0 2px 12px rgba(0,0,0,.08);
}

.dashboard-card h3{
    margin:0 0 15px;
    font-size:18px;
}

.dashboard-item{
    display:flex;
    justify-content:space-between;
    padding:8px 0;
    border-bottom:1px solid #eee;
}

.dashboard-item:last-child{
    border-bottom:none;
}

.dashboard-number{
    font-weight:700;
}

.report-card{
    margin-top:30px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,.08);
    overflow:hidden;
}

.report-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    padding:18px 24px;
    border-bottom:1px solid #eee;
}

.report-header h3{
    margin:0;
    font-size:18px;
    font-weight:600;
}

.report-header span{
    background:#eef5ff;
    color:#2563eb;
    padding:6px 12px;
    border-radius:30px;
    font-size:13px;
    font-weight:600;
}

.table-wrapper{
    overflow-x:auto;
}

.input-search-name {
    height: 42px;
    padding: 0 12px;
    border: 1px solid #dcdfe6;
    border-radius: 10px;
    outline: none;
}

.report-table{
    width:100%;
    border-collapse:collapse;
}

.report-table thead{
    background:#f8fafc;
}

.report-table th{
    padding:14px 16px;
    text-align:left;
    font-size:14px;
    font-weight:600;
    color:#555;
    border-bottom:1px solid #e5e7eb;
}

.report-table td{
    padding:14px 16px;
    border-bottom:1px solid #f1f1f1;
    vertical-align:middle;
}

.report-table tbody tr:hover{
    background:#fafafa;
}

.report-table small{
    color:#888;
}

.status-badge{
    display:inline-block;
    padding:5px 12px;
    border-radius:20px;
    background:#eef6ff;
    color:#2563eb;
    font-size:13px;
    font-weight:600;
}

.empty-state{
    text-align:center;
    padding:50px;
    color:#888;
    font-size:15px;
}

.badge-success{
    background:#dcfce7;
    color:#15803d;
}

.badge-danger{
    background:#fee2e2;
    color:#dc2626;
}

.badge-warning{
    background:#fef3c7;
    color:#b45309;
}
</style>

@endpush



<div class="container dashboard-grid">
    <div class="dashboard-card ">
        <h3>🕒 Khung giờ đặt hôm nay</h3>

        @forelse($todaySchedules as $slot)
            <div class="dashboard-item">
        <div>
            <strong>{{ $slot['time'] }}</strong>
            <div style="width:180px;height:8px;background:#eee;border-radius:10px;margin-top:5px;">
                <div
                    style="
                        width: {{ ($slot['booked'] / 9) * 100 }}%;
                        height:100%;
                        background:#22c55e;
                        border-radius:10px;
                    ">
                </div>
            </div>
        </div>

        <span class="dashboard-number">
            {{ $slot['booked'] }}/9
        </span>
    </div>
        @empty
            <p>Chưa có lịch đặt Hôm nay</p>
        @endforelse
    </div>
</div>

<div class="container dashboard-grid">
    <div class="dashboard-card ">
        <h3>🕒 Khung giờ đặt Ngày mai</h3>

        @forelse($tomorrowSchedules as $slot)
            <div class="dashboard-item">
        <div>
            <strong>{{ $slot['time'] }}</strong>
            <div style="width:180px;height:8px;background:#eee;border-radius:10px;margin-top:5px;">
                <div
                    style="
                        width: {{ ($slot['booked'] / 9) * 100 }}%;
                        height:100%;
                        background:#22c55e;
                        border-radius:10px;
                    ">
                </div>
            </div>
        </div>

        <span class="dashboard-number">
            {{ $slot['booked'] }}/9
        </span>
    </div>
        @empty
            <p>Chưa có lịch đặt Ngày mai</p>
        @endforelse
    </div>
</div>

<div class="container dashboard-grid">
    <div class="dashboard-card ">
        <h3>🕒 Khung giờ đặt Ngày kia</h3>

        @forelse($afterTomorrowSchedules as $slot)
            <div class="dashboard-item">
        <div>
            <strong>{{ $slot['time'] }}</strong>
            <div style="width:180px;height:8px;background:#eee;border-radius:10px;margin-top:5px;">
                <div
                    style="
                        width: {{ ($slot['booked'] / 9) * 100 }}%;
                        height:100%;
                        background:#22c55e;
                        border-radius:10px;
                    ">
                </div>
            </div>
        </div>

        <span class="dashboard-number">
            {{ $slot['booked'] }}/9
        </span>
    </div>
        @empty
            <p>Chưa có lịch đặt Ngày kia</p>
        @endforelse
    </div>
</div>


<form method="GET" class="dashboard-filter container">
    <div>
        <label>Tên phạm nhân</label>
        <input
            type="text"
            name="prisoner_name"
            value="{{ $prisonerName }}"
            placeholder="Nhập tên phạm nhân"
            class="input-search-name"
        >
    </div>    
    <div>
        <label>Từ ngày</label>
        <input
            type="date"
            name="from_date"
            value="{{ $fromDate }}"
        >
    </div>

    <div>
        <label>Đến ngày</label>
        <input
            type="date"
            name="to_date"
            value="{{ $toDate }}"
        >
    </div>

    <button type="submit" class="btn-filter">
        Lọc
    </button>

    <a href="{{ route('admin.thongke.index') }}" class="btn-reset">
        Xóa lọc
    </a>
</form>

<div class="dashboard-grid container">


    <div class="dashboard-card">
        <h3>📰 Tin tức</h3>

        <div class="dashboard-item">
            <span>Tổng bài viết</span>
            <span class="dashboard-number">
                {{ $totalPosts }}
            </span>
        </div>

        <div class="dashboard-item">
            <span>Đã phê duyệt</span>
            <span class="dashboard-number">
                {{ $publishedPosts }}
            </span>
        </div>

        <div class="dashboard-item">
            <span>Chưa phê duyệt</span>
            <span class="dashboard-number">
                {{ $draftPosts }}
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <h3>👤 Người dùng</h3>

        <div class="dashboard-item">
            <span>Tổng người dùng</span>
            <span class="dashboard-number">
                {{ $totalCustomers }}
            </span>
        </div>

        <div class="dashboard-item">
            <span>Đang hoạt động</span>
            <span class="dashboard-number">
                {{ $activeCustomers }}
            </span>
        </div>

        <div class="dashboard-item">
            <span>Bị khóa</span>
            <span class="dashboard-number">
                {{ $lockedCustomers }}
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <h3>📅 Lịch thăm gặp</h3>

        <div class="dashboard-item">
            <span>Tổng lịch đăng ký</span>
            <span class="dashboard-number">
                {{ $totalSchedules }}
            </span>
        </div>
        <div class="dashboard-item">
            <span>Tổng lịch đã duyệt</span>
            <span class="dashboard-number">
                {{ $totalSchedulesPublish }}
            </span>
        </div>
        <div class="dashboard-item">
            <span>Tổng lịch chưa duyệt</span>
            <span class="dashboard-number">
                {{ $totalSchedulesDraft }}
            </span>
        </div>
        <div class="dashboard-item">
            <span>Đã thăm gặp thành công</span>
            <span class="dashboard-number">
                {{ $totalSchedulesDone }}
            </span>
        </div>
    </div>

    <div class="dashboard-card">
        <h3>📜 Nội quy</h3>

        <div class="dashboard-item">
            <span>Tổng số nội quy</span>
            <span class="dashboard-number">
                {{ $totalPrisonRules }}
            </span>
        </div>

        <div class="dashboard-item">
            <span>Đã phê duyệt</span>
            <span class="dashboard-number">
                {{ $publishedPrisonRules }}
            </span>
        </div>

        <div class="dashboard-item">
            <span>Chưa phê duyệt</span>
            <span class="dashboard-number">
                {{ $draftPrisonRules }}
            </span>
        </div>
    </div>

</div>
@if($hasFilter)
<div class="container" style="padding: 10px 30px">
    <div class="report-card ">

        <div class="report-header">
            <h3>Kết quả tìm kiếm</h3>
            <span>{{ $prisoners->count() }} bản ghi</span>
        </div>

        @if($prisoners->isEmpty())
            <div class="empty-state">
                Không tìm thấy dữ liệu.
            </div>
        @else
            <div class="table-wrapper">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Phạm nhân</th>
                            <th>Ngày giờ</th>
                            <th>Thân nhân</th>
                            <th>Quan hệ</th>
                            <th>Số người thăm</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($prisoners as $prisoner)
                            <tr>
                                <td>{{ $prisoner->prisoner_name }}</td>

                                <td>
                                    {{ \Carbon\Carbon::parse($prisoner->visitDate)->format('d/m/Y') }}
                                    <br>
                                    <small>{{ \Carbon\Carbon::parse($prisoner->visitTime)->format('H:i') }}</small>
                                </td>

                                <td>{{ $prisoner->relatives[0]['username'] }}</td>

                                <td>{{ \App\Enums\RelationshipEnum::labelOf($prisoner->relatives[0]['relationship'] ?? null) }}</td>

                                <td>{{ $prisoner->count }}</td>

                                <td>
                                    <span class="status-badge
                                        @if($prisoner->status_label == 'Đã thăm') badge-success
                                        @elseif($prisoner->status_label == 'Từ chối') badge-danger
                                        @else badge-warning
                                        @endif">
                                        {{ $prisoner->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>
@endif

@endsection