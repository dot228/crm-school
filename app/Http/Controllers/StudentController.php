<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\HomeworkSubmission;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\TariffPlan;
use App\Services\WayForPayService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    private function studentId(): int
    {
        return auth()->id();
    }

    private function courseIds()
    {
        return Enrollment::where('student_id', $this->studentId())
            ->with('group')
            ->get()
            ->pluck('group.course_id')
            ->filter()
            ->unique();
    }

    public function schedule(Request $request)
    {
        $viewMode  = $request->get('view', 'list');
        $calPeriod = $request->get('cal_period', 'month');

        $query = Schedule::with(['lesson.course', 'teacher'])
            ->where('student_id', $this->studentId())
            ->where('status', '!=', 'draft');

        if ($viewMode === 'calendar') {
            if ($calPeriod === 'week') {
                $calDate   = \Carbon\Carbon::parse($request->get('cal_date', now()->toDateString()));
                $weekStart = $calDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                $weekEnd   = $weekStart->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $calSchedules = (clone $query)
                    ->whereBetween('scheduled_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orderBy('scheduled_time')->get()
                    ->groupBy(fn($s) => $s->scheduled_date->format('Y-m-d'));
                return view('student.schedule', compact('viewMode', 'calPeriod', 'calDate', 'weekStart', 'weekEnd', 'calSchedules'));
            }
            if ($calPeriod === 'day') {
                $calDate      = \Carbon\Carbon::parse($request->get('cal_date', now()->toDateString()));
                $calSchedules = (clone $query)
                    ->whereDate('scheduled_date', $calDate->toDateString())
                    ->orderBy('scheduled_time')->get();
                return view('student.schedule', compact('viewMode', 'calPeriod', 'calDate', 'calSchedules'));
            }
            $calYear  = (int) $request->get('cal_year',  now()->year);
            $calMonth = (int) $request->get('cal_month', now()->month);
            $calStart = \Carbon\Carbon::create($calYear, $calMonth, 1)->startOfMonth();
            $calEnd   = $calStart->copy()->endOfMonth();
            $calSchedules = (clone $query)
                ->whereBetween('scheduled_date', [$calStart->toDateString(), $calEnd->toDateString()])
                ->orderBy('scheduled_time')->get()
                ->groupBy(fn($s) => $s->scheduled_date->format('Y-m-d'));
            return view('student.schedule', compact('viewMode', 'calPeriod', 'calYear', 'calMonth', 'calSchedules'));
        }

        $schedules = $query
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time', 'desc')
            ->paginate(20);

        return view('student.schedule', compact('schedules', 'viewMode'));
    }

    public function homework()
    {
        $submissions = HomeworkSubmission::with(['lesson.course'])
            ->where('student_id', $this->studentId())
            ->whereNotNull('assignment_text')
            ->orderByRaw("FIELD(status,'submitted','pending','reviewed')")
            ->orderBy('assigned_at', 'desc')
            ->get();

        return view('student.homework', compact('submissions'));
    }

    public function homeworkShow(HomeworkSubmission $submission)
    {
        abort_unless($submission->student_id === $this->studentId(), 403);
        abort_unless($submission->assignment_text !== null, 404);
        $submission->load(['lesson.course']);

        return view('student.homework-show', compact('submission'));
    }

    public function submitHomework(Request $request, HomeworkSubmission $submission)
    {
        abort_unless($submission->student_id === $this->studentId(), 403);
        abort_unless(in_array($submission->status, ['pending', 'submitted']), 403);

        $request->validate([
            'content' => 'nullable|string|max:5000',
            'file'    => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,zip,png,jpg,jpeg',
        ]);

        if ($request->filled('content')) {
            $submission->content = $request->content;
        }

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('homework', 'public');
            $submission->file_path = $path;
        }

        $submission->status       = 'submitted';
        $submission->submitted_at = now();
        $submission->save();

        return redirect()->route('student.homework')
            ->with('success', 'Домашнє завдання здано!');
    }

    public function payments()
    {
        $payments = Payment::with(['enrollment.group.course', 'tariffPlan'])
            ->where('student_id', $this->studentId())
            ->orderBy('payment_date', 'desc')
            ->get();

        $enrollments = Enrollment::with('group.course')
            ->where('student_id', $this->studentId())
            ->where('status', 'active')
            ->get();

        $tariffPlans   = TariffPlan::where('status', 'active')->orderBy('amount')->get();
        $wfpConfigured = app(WayForPayService::class)->isConfigured();
        $moneyBalance  = auth()->user()->money_balance ?? 0;

        return view('student.payments', compact('payments', 'enrollments', 'tariffPlans', 'wfpConfigured', 'moneyBalance'));
    }

    public function payTariff(Request $request)
    {
        $request->validate([
            'enrollment_id'  => 'required|exists:enrollments,id',
            'tariff_plan_id' => 'required|exists:tariff_plans,id',
        ]);

        $enrollment = Enrollment::where('id', $request->enrollment_id)
            ->where('student_id', $this->studentId())
            ->firstOrFail();

        $tariff = TariffPlan::where('id', $request->tariff_plan_id)
            ->where('status', 'active')
            ->firstOrFail();

        Payment::create([
            'student_id'     => $this->studentId(),
            'enrollment_id'  => $enrollment->id,
            'tariff_plan_id' => $tariff->id,
            'lessons_count'  => $tariff->count_lessons,
            'amount'         => $tariff->amount,
            'status'         => 'pending',
            'method'         => 'cash',
            'payment_date'   => now()->toDateString(),
            'notes'          => "Тариф: {$tariff->name} ({$tariff->count_lessons} уроків)",
        ]);

        return redirect()->route('student.payments')
            ->with('success', "Заявку на оплату «{$tariff->name}» надіслано. Очікуйте підтвердження.");
    }

    public function topupMoney(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10',
            'method' => 'required|in:cash,card,bank_transfer,online',
        ]);

        Payment::create([
            'student_id'   => $this->studentId(),
            'payment_type' => 'money',
            'amount'       => $request->amount,
            'status'       => 'pending',
            'method'       => $request->method,
            'payment_date' => now()->toDateString(),
            'notes'        => 'Поповнення грошового балансу',
        ]);

        return redirect()->route('student.payments')
            ->with('success', 'Заявку на поповнення грошового балансу надіслано. Очікуйте підтвердження адміністратора.');
    }
}
