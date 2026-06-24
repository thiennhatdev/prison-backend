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
    padding:30px;
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
            <p>Chưa có lịch đặt hôm nay</p>
        @endforelse
    </div>
</div>

<form method="GET" class="dashboard-filter container">
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
            <span>Đã xuất bản</span>
            <span class="dashboard-number">
                {{ $publishedPosts }}
            </span>
        </div>

        <div class="dashboard-item">
            <span>Bản nháp</span>
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
    </div>

</div>

@endsection