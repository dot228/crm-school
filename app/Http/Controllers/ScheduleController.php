<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\User;
use App\Services\LessonBalanceService;
use App\Services\ZoomService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function __construct(
        private LessonBalanceService $balance,
        private ZoomService          $zoom
    )
    {
    }

    private function isTeacher(): bool
    {
        return auth()->user()->isTeacher();
    }

    private function assertOwnsSchedule(Schedule $schedule): void
    {
        if ($this->isTeacher() && $schedule->teacher_id !== auth()->id()) {
            abort(403);
        }
    }

    private function teacherStudentIds(): array
    {
        return \App\Models\Enrollment::whereHas('group', function ($q) {
            $q->where('teacher_id', auth()->id());
        })->pluck('student_id')->unique()->values()->toArray();
    }

    private function teacherLessons()
    {
        $courseIds = \App\Models\Course::where('teacher_id', auth()->id())
            ->orWhereHas('groups', fn($q) => $q->where('teacher_id', auth()->id()))
            ->pluck('id');
        return Lesson::with('course')->whereIn('course_id', $courseIds)->orderBy('scheduled_at', 'desc')->get();
    }

    public function index(Request $request)
    {
        $viewMode = $request->get('view', 'list');

        $query = Schedule::with(['lesson.course', 'student', 'teacher'])
            ->orderBy('scheduled_date', 'asc')
            ->orderBy('scheduled_time', 'asc');

        if ($this->isTeacher()) {
            $query->where('teacher_id', auth()->id());
        } else {
            if ($request->filled('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }
            if ($request->filled('student_id')) {
                $query->where('student_id', $request->student_id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $teachers = $this->isTeacher() ? collect() : User::where('role_id', User::ROLE_TEACHER)->orderBy('name')->get();
        $students = $this->isTeacher()
            ? User::whereIn('id', $this->teacherStudentIds())->orderBy('name')->get()
            : User::where('role_id', User::ROLE_STUDENT)->orderBy('name')->get();

        if ($viewMode === 'calendar') {
            $calPeriod = $request->get('cal_period', 'month');

            if ($calPeriod === 'week') {
                $calDate   = \Carbon\Carbon::parse($request->get('cal_date', now()->toDateString()));
                $weekStart = $calDate->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
                $weekEnd   = $weekStart->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);
                $calSchedules = (clone $query)
                    ->whereBetween('scheduled_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                    ->orderBy('scheduled_time')->get()
                    ->groupBy(fn($s) => $s->scheduled_date->format('Y-m-d'));
                return view('schedules.index', compact('calSchedules', 'teachers', 'students', 'viewMode', 'calPeriod', 'calDate', 'weekStart', 'weekEnd'));
            }

            if ($calPeriod === 'day') {
                $calDate      = \Carbon\Carbon::parse($request->get('cal_date', now()->toDateString()));
                $calSchedules = (clone $query)
                    ->whereDate('scheduled_date', $calDate->toDateString())
                    ->orderBy('scheduled_time')->get();
                return view('schedules.index', compact('calSchedules', 'teachers', 'students', 'viewMode', 'calPeriod', 'calDate'));
            }

            // month (default)
            $calYear  = (int) $request->get('cal_year',  now()->year);
            $calMonth = (int) $request->get('cal_month', now()->month);
            $calStart = \Carbon\Carbon::create($calYear, $calMonth, 1)->startOfMonth();
            $calEnd   = $calStart->copy()->endOfMonth();
            $calSchedules = (clone $query)
                ->whereBetween('scheduled_date', [$calStart->toDateString(), $calEnd->toDateString()])
                ->get()
                ->groupBy(fn($s) => $s->scheduled_date->format('Y-m-d'));
            return view('schedules.index', compact('calSchedules', 'teachers', 'students', 'viewMode', 'calPeriod', 'calYear', 'calMonth'));
        }

        if ($request->filled('date')) {
            $query->where('scheduled_date', $request->date);
        }

        $schedules = $query->paginate(20);
        return view('schedules.index', compact('schedules', 'teachers', 'students', 'viewMode'));
    }

    public function create()
    {
        $zoomConfigured = $this->zoom->isConfigured();

        if ($this->isTeacher()) {
            $teacher = auth()->user();
            $lessons = $this->teacherLessons();
            $students = User::whereIn('id', $this->teacherStudentIds())->orderBy('name')->get();
            $teachers = collect([$teacher]);
        } else {
            $lessons = Lesson::with('course')->orderBy('scheduled_at', 'desc')->get();
            $students = User::where('role_id', User::ROLE_STUDENT)->orderBy('name')->get();
            $teachers = User::where('role_id', User::ROLE_TEACHER)->orderBy('name')->get();
        }

        return view('schedules.create', compact('lessons', 'students', 'teachers', 'zoomConfigured'));
    }

    public function store(Request $request)
    {
        $isAdmin = !$this->isTeacher();
        $generateZoom = $request->boolean('generate_zoom');

        $data = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'student_id' => 'required|exists:users,id',
            'teacher_id' => 'required|exists:users,id',
            'substitute_teacher_id' => 'nullable|exists:users,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'absence_type' => 'nullable|in:excused,unexcused',
            'absence_reason' => 'nullable|string|max:1000',
            'zoom_link' => 'nullable|url|max:500',
            'notes' => 'nullable|string',
        ]);

        if ($this->isTeacher()) {
            $data['teacher_id'] = auth()->id();
            unset($data['substitute_teacher_id']);
        }

        if (in_array($data['status'], ['scheduled', 'completed'])) {
            if (!$this->balance->hasBalance($data['student_id'], $data['lesson_id'])) {
                return redirect()->back()->withInput()
                    ->withErrors(['student_id' => 'Баланс уроків студента = 0. Поповніть баланс перед плануванням.']);
            }
        }

        if ($this->zoom->isConfigured() && empty($data['zoom_link']) && $generateZoom) {
            $lesson = Lesson::with('course')->find($data['lesson_id']);
            $startTime = $data['scheduled_date'] . 'T' . $data['scheduled_time'];
            $joinUrl = $this->zoom->createMeeting(
                ($lesson->course->title ?? '') . ' — ' . ($lesson->title ?? ''),
                $startTime,
                (int)$data['duration_minutes']
            );
            if ($joinUrl) {
                $data['zoom_link'] = $joinUrl;
            }
        }

        $schedule = Schedule::create($data);

        if ($schedule->shouldDeductBalance()) {
            $schedule->load('lesson');
            $this->balance->deductForSchedule($schedule);
        }

        return redirect()->route('schedules.index')
            ->with('success', 'Запис у розкладі створено.');
    }

    public function show(Schedule $schedule)
    {
        $this->assertOwnsSchedule($schedule);
        $schedule->load(['lesson.course', 'student', 'teacher']);
        return view('schedules.show', compact('schedule'));
    }

    public function edit(Schedule $schedule)
    {
        $this->assertOwnsSchedule($schedule);
        $zoomConfigured = $this->zoom->isConfigured();

        if ($this->isTeacher()) {
            $lessons = $this->teacherLessons();
            $students = User::whereIn('id', $this->teacherStudentIds())->orderBy('name')->get();
            $teachers = collect([auth()->user()]);
        } else {
            $lessons = Lesson::with('course')->orderBy('scheduled_at', 'desc')->get();
            $students = User::where('role_id', User::ROLE_STUDENT)->orderBy('name')->get();
            $teachers = User::where('role_id', User::ROLE_TEACHER)->orderBy('name')->get();
        }

        return view('schedules.edit', compact('schedule', 'lessons', 'students', 'teachers', 'zoomConfigured'));
    }

    public function update(Request $request, Schedule $schedule)
    {
        $this->assertOwnsSchedule($schedule);

        $oldStatus = $schedule->status;
        $generateZoom = $request->boolean('generate_zoom');

        $data = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
            'student_id' => 'required|exists:users,id',
            'teacher_id' => 'required|exists:users,id',
            'substitute_teacher_id' => 'nullable|exists:users,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required',
            'duration_minutes' => 'required|integer|min:15|max:480',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'absence_type' => 'nullable|in:excused,unexcused',
            'absence_reason' => 'nullable|string|max:1000',
            'zoom_link' => 'nullable|url|max:500',
            'notes' => 'nullable|string',
        ]);

        if ($this->isTeacher()) {
            $data['teacher_id'] = auth()->id();
            unset($data['substitute_teacher_id']);
        }

        if (in_array($data['status'], ['scheduled', 'completed'])) {
            if (!$this->balance->hasBalance($data['student_id'], $data['lesson_id'], $schedule->id)) {
                return redirect()->back()->withInput()
                    ->withErrors(['student_id' => 'Баланс уроків студента вичерпано (з урахуванням запланованих).']);
            }
        }

        if ($generateZoom && $this->zoom->isConfigured() && empty($data['zoom_link'])) {
            if ($schedule->zoom_link) {
                $this->zoom->deleteMeeting($schedule->zoom_link);
            }
            $lesson = Lesson::with('course')->find($data['lesson_id']);
            $startTime = $data['scheduled_date'] . 'T' . $data['scheduled_time'];
            $joinUrl = $this->zoom->createMeeting(
                ($lesson->course->title ?? '') . ' — ' . ($lesson->title ?? ''),
                $startTime,
                (int)$data['duration_minutes']
            );
            if ($joinUrl) {
                $data['zoom_link'] = $joinUrl;
            }
        }

        $schedule->update($data);
        $schedule->refresh()->load('lesson');

        $wasDeductible = in_array($oldStatus, ['completed'])
            || ($oldStatus === 'no_show');
        $nowDeductible = $schedule->shouldDeductBalance();

        if (!$wasDeductible && $nowDeductible) {
            $this->balance->deductForSchedule($schedule);
        }

        return redirect()->route('schedules.index')
            ->with('success', 'Розклад оновлено.');
    }

    public function updateStatus(Request $request, Schedule $schedule)
    {
        $this->assertOwnsSchedule($schedule);

        $oldStatus = $schedule->status;

        $request->validate([
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
            'absence_type' => 'nullable|in:excused,unexcused',
            'absence_reason' => 'nullable|string|max:1000',
        ]);

        if ($request->status === 'completed' && $schedule->status !== 'completed') {
            $schedule->loadMissing('lesson');
            if (!$this->balance->hasBalance($schedule->student_id, $schedule->lesson_id)) {
                return redirect()->back()
                    ->withErrors(['balance' => 'Баланс уроків = 0. Поповніть баланс студента.']);
            }
        }

        $wasDeductible = $schedule->shouldDeductBalance();

        $schedule->update([
            'status' => $request->status,
            'absence_type' => $request->absence_type,
            'absence_reason' => $request->absence_reason,
        ]);

        $schedule->refresh()->load('lesson');
        $nowDeductible = $schedule->shouldDeductBalance();

        if (!$wasDeductible && $nowDeductible) {
            $this->balance->deductForSchedule($schedule);
        }

        return redirect()->back()->with('success', 'Статус уроку оновлено.');
    }

    public function destroy(Schedule $schedule)
    {
        $this->assertOwnsSchedule($schedule);

        if ($schedule->zoom_link && $this->zoom->isConfigured()) {
            $this->zoom->deleteMeeting($schedule->zoom_link);
        }

        $schedule->delete();
        return redirect()->route('schedules.index')
            ->with('success', 'Запис видалено.');
    }
}
