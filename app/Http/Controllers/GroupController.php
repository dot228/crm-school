<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Services\CourseService;
use App\Services\GroupService;
use App\Services\UserService;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct(
        protected GroupService $groupService,
        protected CourseService $courseService,
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['course_id', 'teacher_id', 'status', 'search']);
        $groups  = $this->groupService->getList($filters);
        return view('groups.index', compact('groups', 'filters'));
    }

    public function create()
    {
        $courses  = $this->courseService->getActiveCourses();
        $teachers = $this->userService->getTeachers();
        return view('groups.create', compact('courses', 'teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'course_id'    => ['required', 'exists:courses,id'],
            'teacher_id'   => ['nullable', 'exists:users,id'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_students' => ['required', 'integer', 'min:1'],
            'status'       => ['required', 'in:active,completed,cancelled'],
            'notes'        => ['nullable', 'string'],
        ]);

        $this->groupService->create($data);
        return redirect()->route('groups.index')->with('success', 'Групу створено успішно.');
    }

    public function show(int $id)
    {
        $group = $this->groupService->findById($id);
        abort_if(!$group, 404);
        return view('groups.show', compact('group'));
    }

    public function edit(int $id)
    {
        $group    = $this->groupService->findById($id);
        abort_if(!$group, 404);
        $courses  = $this->courseService->getActiveCourses();
        $teachers = $this->userService->getTeachers();
        return view('groups.edit', compact('group', 'courses', 'teachers'));
    }

    public function update(Request $request, int $id)
    {
        $group = $this->groupService->findById($id);
        abort_if(!$group, 404);

        $data = $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'course_id'    => ['required', 'exists:courses,id'],
            'teacher_id'   => ['nullable', 'exists:users,id'],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => ['nullable', 'date', 'after_or_equal:start_date'],
            'max_students' => ['required', 'integer', 'min:1'],
            'status'       => ['required', 'in:active,completed,cancelled'],
            'notes'        => ['nullable', 'string'],
        ]);

        $this->groupService->update($group, $data);
        return redirect()->route('groups.index')->with('success', 'Групу оновлено успішно.');
    }

    public function destroy(int $id)
    {
        $group = $this->groupService->findById($id);
        abort_if(!$group, 404);
        $this->groupService->delete($group);
        return redirect()->route('groups.index')->with('success', 'Групу видалено.');
    }
}
