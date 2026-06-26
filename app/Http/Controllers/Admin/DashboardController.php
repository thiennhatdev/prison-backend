<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Customer;
use App\Models\VisitationSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    private function getScheduleStats(Carbon $date)
{
    return VisitationSchedule::query()
        ->published()
        ->whereDate('visitDate', $date)
        ->select(
            'visitTime',
            DB::raw('COUNT(*) as booked_count')
        )
        ->groupBy('visitTime')
        ->orderBy('visitTime')
        ->get()
        ->map(function ($item) {
            return [
                'time' => Carbon::parse($item->visitTime)->format('H:i'),
                'booked' => $item->booked_count,
                'available' => 9 - $item->booked_count,
                'total' => 9,
            ];
        });
}

    public function index(Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $postQuery = Post::query();
        $customerQuery = Customer::query();
        $scheduleQuery = VisitationSchedule::query();

        if ($fromDate) {
            $postQuery->whereDate('created_at', '>=', $fromDate);
            $customerQuery->whereDate('created_at', '>=', $fromDate);
            $scheduleQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $postQuery->whereDate('created_at', '<=', $toDate);
            $customerQuery->whereDate('created_at', '<=', $toDate);
            $scheduleQuery->whereDate('created_at', '<=', $toDate);
        }

        $today = $this->getScheduleStats(now('Asia/Ho_Chi_Minh'));
        $tomorrow = $this->getScheduleStats(now('Asia/Ho_Chi_Minh')->addDay());
        $afterTomorrow = $this->getScheduleStats(now('Asia/Ho_Chi_Minh')->addDay(2));

        return view('admin.thongke.index', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,

            'totalPosts' => (clone $postQuery)->count(),
            'publishedPosts' => (clone $postQuery)
                ->published()
                ->count(),
            'draftPosts' => (clone $postQuery)
                ->where(function ($q) {
                    $q->whereNull('published')
                        ->orWhere('published', 0);
                })
                ->count(),

            'totalCustomers' => (clone $customerQuery)->count(),
            'activeCustomers' => (clone $customerQuery)
                ->where('is_active', 1)
                ->count(),
            'lockedCustomers' => (clone $customerQuery)
                ->where('is_active', 0)
                ->count(),

            'totalSchedules' => (clone $scheduleQuery)->count(),
            'totalSchedulesPublish' => (clone $scheduleQuery)
                ->published()
                ->count(),
            'totalSchedulesDraft' => (clone $scheduleQuery)
                ->where(function ($q) {
                    $q->whereNull('published')
                        ->orWhere('published', 0);
                })
                ->count(),
            'totalSchedulesDone' => (clone $scheduleQuery)
                ->published()
                ->where('status', 'DONE')
                ->count(),
            'todaySchedules' => $today,
            'tomorrowSchedules' => $tomorrow,
            'afterTomorrowSchedules' => $afterTomorrow,

        ]);
    }
}