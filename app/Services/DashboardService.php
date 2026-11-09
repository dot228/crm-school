<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Payment;
use App\Models\User;

class DashboardService
{
    public function getStats(): array
    {
        return [
            'total_students'   => User::where('role_id', User::ROLE_STUDENT)->count(),
            'total_teachers'   => User::where('role_id', User::ROLE_TEACHER)->count(),
            'total_courses'    => Course::where('status', 'active')->count(),
            'total_groups'     => Group::where('status', 'active')->count(),
            'total_revenue'    => (float) Payment::where('status', 'paid')->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'new_enrollments'  => Enrollment::whereMonth('enrolled_at', now()->month)->count(),
            'active_enrollments' => Enrollment::where('status', 'active')->count(),
        ];
    }

    public function getRecentEnrollments(int $limit = 5)
    {
        return Enrollment::with(['student', 'group.course'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getRecentPayments(int $limit = 5)
    {
        return Payment::with(['student', 'enrollment.group.course'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getMonthlyRevenue(): array
    {
        $data = [];
        for ($i = 11; $i >= 0; $i--) {
            $date    = now()->subMonths($i);
            $amount  = Payment::where('status', 'paid')
                ->whereYear('payment_date', $date->year)
                ->whereMonth('payment_date', $date->month)
                ->sum('amount');
            $data[] = [
                'month'  => $date->format('M Y'),
                'amount' => (float) $amount,
            ];
        }
        return $data;
    }
}
