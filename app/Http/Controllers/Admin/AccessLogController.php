<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccessLog;
use Illuminate\Http\Request;

class AccessLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'ip' => ['nullable', 'ip'],
            'from_date' => ['nullable', 'date_format:Y-m-d'],
            'to_date' => ['nullable', 'date_format:Y-m-d', ...($request->filled('from_date') ? ['after_or_equal:from_date'] : [])],
            'errors_only' => ['nullable', 'in:1'],
        ]);

        $logs = AccessLog::query()
            ->when($filters['ip'] ?? null, fn ($query, $ip) => $query->where('ip', $ip))
            ->when($filters['from_date'] ?? null, fn ($query, $date) => $query->whereDate('accessed_at', '>=', $date))
            ->when($filters['to_date'] ?? null, fn ($query, $date) => $query->whereDate('accessed_at', '<=', $date))
            ->when($request->boolean('errors_only'), fn ($query) => $query->where('status', '>=', 400))
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.access-logs.index', compact('logs'));
    }
}
