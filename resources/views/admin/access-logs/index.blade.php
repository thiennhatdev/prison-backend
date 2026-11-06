@extends('twill::layouts.free')

@push('extra_css')
<link rel="stylesheet" href="{{ asset('css/access-logs.css') }}?v=2">
@endpush

@section('customPageContent')
<div class="access-log-page">
    <div class="access-log-heading"><span class="access-log-eyebrow">GIÁM SÁT HỆ THỐNG</span><h1>Nhật ký truy cập</h1></div>
    <p>Truy cập website/API đi qua ứng dụng · Giờ Việt Nam (UTC+7) · Giữ nhật ký 7 ngày, tự dọn mỗi ngày.</p>
    @if($errors->any())
        <div class="access-log-alert" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>
    @endif
    <form method="GET" class="access-log-filter">
        <div><label for="ip">IP truy cập</label><input id="ip" name="ip" value="{{ request('ip') }}" placeholder="Ví dụ: 127.0.0.1"></div>
        <div><label for="from_date">Từ ngày</label><input id="from_date" type="date" name="from_date" value="{{ request('from_date') }}"></div>
        <div><label for="to_date">Đến ngày</label><input id="to_date" type="date" name="to_date" value="{{ request('to_date') }}"></div>
        <label class="access-log-checkbox"><input type="checkbox" name="errors_only" value="1" @checked(request('errors_only') === '1')> Chỉ request lỗi (400–599)</label>
        <button type="submit">Lọc</button>
        <a href="{{ route('admin.access-logs.index') }}">Xóa lọc</a>
    </form>
    <p class="access-log-count">Tổng cộng: {{ number_format($logs->total(), 0, ',', '.') }} request</p>
    <div class="access-log-table">
        <table>
            <thead><tr><th>Thời gian</th><th>IP</th><th>Phương thức</th><th>Đường dẫn</th><th>Mã phản hồi</th><th>Xử lý</th><th>Tài khoản</th><th>Thiết bị / trình duyệt (User-Agent)</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td>{{ $log->accessed_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $log->ip }}</td>
                    <td><span class="access-log-method">{{ $log->method }}</span></td>
                    <td class="access-log-path">{{ $log->path }}</td>
                    <td><span class="access-log-status {{ $log->status >= 400 ? 'access-log-error' : 'access-log-success' }}">{{ $log->status }}</span></td>
                    <td>{{ $log->duration_ms }} ms</td>
                    <td>{{ $log->account_type === 'admin' ? 'Quản trị' : ($log->account_type ? 'Người dùng' : 'Khách') }}@if($log->account_id)<br>{{ $log->account_name }} (#{{ $log->account_id }})@endif</td>
                    <td class="access-log-agent">{{ $log->user_agent ?: 'Không xác định' }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Chưa có nhật ký phù hợp.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="access-log-pagination">
        @if($logs->previousPageUrl())<a href="{{ $logs->previousPageUrl() }}">← Trang trước</a>@endif
        <span>Trang {{ $logs->currentPage() }} / {{ $logs->lastPage() }}</span>
        @if($logs->nextPageUrl())<a href="{{ $logs->nextPageUrl() }}">Trang sau →</a>@endif
    </div>
</div>
@endsection
