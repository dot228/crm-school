<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\CourseService;
use App\Services\GroupService;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function __construct(
        protected CourseService $courseService,
        protected GroupService $groupService
    ) {}

    public function index(Request $request)
    {
        $query = \App\Models\Lesson::with(['course', 'group']);

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $lessons  = $query->orderBy('scheduled_at')->paginate(15);
        $courses  = $this->courseService->getActiveCourses();
        $filters  = $request->only(['course_id', 'group_id', 'status']);
        return view('lessons.index', compact('lessons', 'courses', 'filters'));
    }

    public function create()
    {
        $courses = $this->courseService->getActiveCourses();
        $statuses = Lesson::STATUSES;
        return view('lessons.create', compact('courses', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'course_id'        => ['required', 'exists:courses,id'],
            'group_id'         => ['nullable', 'exists:groups,id'],
            'order'            => ['required', 'integer', 'min:0'],
            'scheduled_at'     => ['nullable', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'status'           => ['required', 'in:scheduled,completed,cancelled'],
            'zoom_link'        => ['nullable', 'url'],
            'homework'         => ['nullable', 'string'],
        ]);

        Lesson::create($data);
        return redirect()->route('lessons.index')->with('success', 'Урок створено успішно.');
    }

    public function show(int $id)
    {
        $lesson = Lesson::with(['course', 'group', 'contents'])->findOrFail($id);
        return view('lessons.show', compact('lesson'));
    }

    public function edit(int $id)
    {
        $lesson   = Lesson::findOrFail($id);
        $courses  = $this->courseService->getActiveCourses();
        $groups   = $this->groupService->getByCourse($lesson->course_id);
        $statuses = Lesson::STATUSES;
        return view('lessons.edit', compact('lesson', 'courses', 'groups', 'statuses'));
    }

    public function update(Request $request, int $id)
    {
        $lesson = Lesson::findOrFail($id);

        $data = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string'],
            'course_id'        => ['required', 'exists:courses,id'],
            'group_id'         => ['nullable', 'exists:groups,id'],
            'order'            => ['required', 'integer', 'min:0'],
            'scheduled_at'     => ['nullable', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:1'],
            'status'           => ['required', 'in:scheduled,completed,cancelled'],
            'zoom_link'        => ['nullable', 'url'],
            'homework'         => ['nullable', 'string'],
        ]);

        $lesson->update($data);
        return redirect()->route('lessons.index')->with('success', 'Урок оновлено успішно.');
    }

    public function destroy(int $id)
    {
        Lesson::findOrFail($id)->delete();
        return redirect()->route('lessons.index')->with('success', 'Урок видалено.');
    }
}
