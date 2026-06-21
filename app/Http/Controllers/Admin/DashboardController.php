<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Customer;
use App\Models\VisitationSchedule;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
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
        ]);
    }
}