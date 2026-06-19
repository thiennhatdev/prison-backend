<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Customer;
use App\Models\VisitationSchedule;

class DashboardController extends Controller
{
        public function index()
    {
        return view('admin.thongke.index', [
            'totalPosts' => Post::count(),
            'publishedPosts' => Post::published()->count(),
            'draftPosts' => Post::whereNull('published')
                ->orWhere('published', 0)
                ->count(),

            'totalCustomers' => Customer::count(),
            'activeCustomers' => Customer::where('is_active', 1)->count(),
            'lockedCustomers' => Customer::where('is_active', 0)->count(),

            'totalSchedules' => VisitationSchedule::count(),
            'totalSchedulesPublish' => VisitationSchedule::published()->count(),
            'totalSchedulesDraft' => VisitationSchedule::whereNull('published')
                ->orWhere('published', 0)
                ->count(),
        ]);
    }
}