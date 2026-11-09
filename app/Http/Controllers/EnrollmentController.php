<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Services\EnrollmentService;
use App\Services\GroupService;
use App\Services\UserService;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(
        protected EnrollmentService $enrollmentService,
        protected GroupService $groupService,
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        $filters     = $request->only(['group_id', 'student_id', 'status']);
        $enrollments = $this->enrollmentService->getList($filters);
        return view('enrollments.index', compact('enrollments', 'filters'));
    }

    public function create()
    {
        $students = $this->userService->getStudents();
        $groups   = \App\Models\Group::with('course')->where('status', 'active')->get();
        return view('enrollments.create', compact('students', 'groups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'student_id'  => ['required', 'exists:users,id'],
            'group_id'    => ['required', 'exists:groups,id'],
            'status'      => ['required', 'in:active,completed,cancelled,suspended'],
            'enrolled_at' => ['required', 'date'],
            'notes'       => ['nullable', 'string'],
        ]);

        $this->enrollmentService->create($data);
        return redirect()->route('enrollments.index')->with('success', 'Зарахування створено успішно.');
    }

    public function show(int $id)
    {
        $enrollment = $this->enrollmentService->findById($id);
        abort_if(!$enrollment, 404);
        return view('enrollments.show', compact('enrollment'));
    }

    public function edit(int $id)
    {
        $enrollment = $this->enrollmentService->findById($id);
        abort_if(!$enrollment, 404);
        $students   = $this->userService->getStudents();
        $groups     = \App\Models\Group::with('course')->where('status', 'active')->get();
        return view('enrollments.edit', compact('enrollment', 'students', 'groups'));
    }

    public function update(Request $request, int $id)
    {
        $enrollment = $this->enrollmentService->findById($id);
        abort_if(!$enrollment, 404);

        $data = $request->validate([
            'student_id'  => ['required', 'exists:users,id'],
            'group_id'    => ['required', 'exists:groups,id'],
            'status'      => ['required', 'in:active,completed,cancelled,suspended'],
            'enrolled_at' => ['required', 'date'],
            'notes'       => ['nullable', 'string'],
        ]);

        $this->enrollmentService->update($enrollment, $data);
        return redirect()->route('enrollments.index')->with('success', 'Зарахування оновлено успішно.');
    }

    public function destroy(int $id)
    {
        $enrollment = $this->enrollmentService->findById($id);
        abort_if(!$enrollment, 404);
        $this->enrollmentService->delete($enrollment);
        return redirect()->route('enrollments.index')->with('success', 'Зарахування видалено.');
    }
}
