@extends('twill::layouts.free')

@section('content')



@push('extra_css')
<style>
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