<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Customer;
use App\Models\VisitationSchedule;
use App\Models\PrisonRule;
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
            'visitEndTime',
            DB::raw('COUNT(*) as booked_count')
        )
        ->groupBy('visitTime', 'visitEndTime')
        ->orderBy('visitTime')
        ->orderBy('visitEndTime')
        ->get()
        ->map(function ($item) {
            return [
                'time' => Carbon::parse($item->visitTime)->format('H:i'),
                'endTime' => $item->visitEndTime
                    ? Carbon::parse($item->visitEndTime)->format('H:i')
                    : null,
                'booked' => $item->booked_count,
                'available' => max(0, 9 - $item->booked_count),
                'total' => 9,
            ];
        });
}

    public function index(Request $request)
    {
        $prisonerName = $request->search;

        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        $postQuery = Post::query();
        $customerQuery = Customer::query();
        $scheduleQuery = VisitationSchedule::query();
        $scheduleQuery2 = VisitationSchedule::query();
        $prisonRuleQuery = PrisonRule::query();

        $hasFilter = $request->filled('search')
            || $request->filled('from_date')
            || $request->filled('to_date');

        $prisoners = collect();

        if ($hasFilter) {
            $listQuery = VisitationSchedule::query()->with('customer');

            if ($prisonerName) {
                $listQuery->where('prisoner_name', 'like', "%{$prisonerName}%");
            }

            if ($fromDate) {
                $listQuery->whereDate('visitDate', '>=', $fromDate);
            }

            if ($toDate) {
                $listQuery->whereDate('visitDate', '<=', $toDate);
            }

            $prisoners = $listQuery
                ->orderBy('visitDate')
                ->orderBy('visitTime')
                ->get();

        }

        if ($fromDate) {
            $postQuery->whereDate('created_at', '>=', $fromDate);
            $customerQuery->whereDate('created_at', '>=', $fromDate);
            $scheduleQuery->whereDate('created_at', '>=', $fromDate);
            $scheduleQuery2->whereDate('visitDate', '>=', $fromDate);
            $prisonRuleQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $postQuery->whereDate('created_at', '<=', $toDate);
            $customerQuery->whereDate('created_at', '<=', $toDate);
            $scheduleQuery->whereDate('created_at', '<=', $toDate);
            $scheduleQuery2->whereDate('visitDate', '<=', $toDate);
            $prisonRuleQuery->whereDate('created_at', '<=', $toDate);
        }

        $today = $this->getScheduleStats(now('Asia/Ho_Chi_Minh'));
        $tomorrow = $this->getScheduleStats(now('Asia/Ho_Chi_Minh')->addDay());
        $afterTomorrow = $this->getScheduleStats(now('Asia/Ho_Chi_Minh')->addDay(2));

        $prisonerStats = (clone $scheduleQuery2)
            ->published()
            ->select('prisoner_id', 'relatives')
            ->get()
            ->groupBy('prisoner_id');

        $totalVisitedPrisoners = $prisonerStats->count();

        $totalVisitedRelatives = $prisonerStats->sum(function ($schedules) {
            return $schedules->sum(function ($schedule) {
                return count($schedule->relatives ?? []);
            });
        });

        return view('admin.thongke.index', [
            'search' => $prisonerName,
            'hasFilter' => $hasFilter,
            'prisoners' => $prisoners,

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
            'totalPrisonRules' => (clone $prisonRuleQuery)->count(),
            'publishedPrisonRules' => (clone $prisonRuleQuery)
                ->published()
                ->count(),
            'draftPrisonRules' => (clone $prisonRuleQuery)
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

            'totalVisitedPrisoners' => $totalVisitedPrisoners,
            'totalVisitedRelatives' => $totalVisitedRelatives,
        ]);
    }
}