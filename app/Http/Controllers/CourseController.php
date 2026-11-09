<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\CourseService;
use App\Services\UserService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService,
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'level', 'search', 'teacher_id']);
        $courses = $this->courseService->getList($filters);
        return view('courses.index', compact('courses', 'filters'));
    }

    public function create()
    {
        $teachers = $this->userService->getTeachers();
        $levels   = Course::LEVELS;
        return view('courses.create', compact('teachers', 'levels'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'lesson_price'   => ['nullable', 'numeric', 'min:0'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'level'          => ['required', 'in:beginner,intermediate,advanced'],
            'status'         => ['required', 'in:active,inactive,archived'],
            'teacher_id'     => ['nullable', 'exists:users,id'],
            'image_file'     => ['nullable', 'image', 'max:2048'],
        ]);

        $this->courseService->create($data);
        return redirect()->route('courses.index')->with('success', 'Курс створено успішно.');
    }

    public function show(int $id)
    {
        $course = $this->courseService->findById($id);
        abort_if(!$course, 404);
        return view('courses.show', compact('course'));
    }

    public function edit(int $id)
    {
        $course   = $this->courseService->findById($id);
        abort_if(!$course, 404);
        $teachers = $this->userService->getTeachers();
        $levels   = Course::LEVELS;
        return view('courses.edit', compact('course', 'teachers', 'levels'));
    }

    public function update(Request $request, int $id)
    {
        $course = $this->courseService->findById($id);
        abort_if(!$course, 404);

        $data = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'lesson_price'   => ['nullable', 'numeric', 'min:0'],
            'duration_hours' => ['required', 'integer', 'min:1'],
            'level'          => ['required', 'in:beginner,intermediate,advanced'],
            'status'         => ['required', 'in:active,inactive,archived'],
            'teacher_id'     => ['nullable', 'exists:users,id'],
            'image_file'     => ['nullable', 'image', 'max:2048'],
        ]);

        $this->courseService->update($course, $data);
        return redirect()->route('courses.index')->with('success', 'Курс оновлено успішно.');
    }

    public function destroy(int $id)
    {
        $course = $this->courseService->findById($id);
        abort_if(!$course, 404);
        $this->courseService->delete($course);
        return redirect()->route('courses.index')->with('success', 'Курс видалено.');
    }
}
