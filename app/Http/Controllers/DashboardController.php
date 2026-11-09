<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboardService) {}

    public function index()
    {
        $user = auth()->user();

        if ($user->isTeacher()) {
            return redirect()->route('teacher.students');
        }

        if ($user->isStudent()) {
            return redirect()->route('student.schedule');
        }

        $stats             = $this->dashboardService->getStats();
        $recentEnrollments = $this->dashboardService->getRecentEnrollments();
        $recentPayments    = $this->dashboardService->getRecentPayments();
        $monthlyRevenue    = $this->dashboardService->getMonthlyRevenue();

        return view('dashboard.index', compact(
            'stats', 'recentEnrollments', 'recentPayments', 'monthlyRevenue'
        ));
    }
}
