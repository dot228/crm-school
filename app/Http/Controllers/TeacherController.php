<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    private function teacherCourseIds()
    {
        return Course::where('teacher_id', auth()->id())
            ->orWhereHas('groups', fn($q) => $q->where('teacher_id', auth()->id()))
            ->pluck('id');
    }

    public function students()
    {
        $studentIds = Enrollment::whereHas('group', function ($q) {
            $q->where('teacher_id', auth()->id());
        })->pluck('student_id')->unique();

        $students = User::whereIn('id', $studentIds)
            ->with(['enrollments.group.course', 'payments'])
            ->orderBy('name')
            ->get();

        return view('teacher.students', compact('students'));
    }

    public function studentShow(int $id)
    {
        $studentIds = Enrollment::whereHas('group', function ($q) {
            $q->where('teacher_id', auth()->id());
        })->pluck('student_id')->unique();

        $student = User::whereIn('id', $studentIds)
            ->with(['enrollments.group.course', 'payments', 'homeworkSubmissions.lesson'])
            ->findOrFail($id);

        return view('teacher.student-show', compact('student'));
    }

    public function lessons()
    {
        $courseIds = $this->teacherCourseIds();

        $courses = Course::whereIn('id', $courseIds)
            ->with(['lessons' => fn($q) => $q->orderBy('order')->with('contents')])
            ->orderBy('title')
            ->get();

        return view('teacher.lessons', compact('courses'));
    }

    public function homeworkEdit(Lesson $lesson)
    {
        abort_unless($this->teacherCourseIds()->contains($lesson->course_id), 403);

        $lesson->load('course');

        $studentIds = \App\Models\Enrollment::whereHas('group', function ($q) use ($lesson) {
            $q->where('course_id', $lesson->course_id);
        })->pluck('student_id')->unique();

        $students = User::whereIn('id', $studentIds)->orderBy('name')->get();

        $existing = \App\Models\HomeworkSubmission::where('lesson_id', $lesson->id)
            ->whereIn('student_id', $studentIds)
            ->get()
            ->keyBy('student_id');

        return view('teacher.homework-assign', compact('lesson', 'students', 'existing'));
    }

    public function homeworkUpdate(Request $request, Lesson $lesson)
    {
        abort_unless($this->teacherCourseIds()->contains($lesson->course_id), 403);

        $request->validate([
            'assignments'                => 'nullable|array',
            'assignments.*.student_id'   => 'required|exists:users,id',
            'assignments.*.text'         => 'nullable|string|max:5000',
            'assignments.*.file'         => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,zip,png,jpg,jpeg',
        ]);

        foreach ($request->input('assignments', []) as $i => $item) {
            $text      = trim($item['text'] ?? '');
            $studentId = $item['student_id'];
            $file      = $request->file("assignments.{$i}.file");

            if ($text === '' && !$file) {
                \App\Models\HomeworkSubmission::where('lesson_id', $lesson->id)
                    ->where('student_id', $studentId)
                    ->whereIn('status', ['pending'])
                    ->delete();
                continue;
            }

            $updateData = [
                'assignment_text' => $text ?: null,
                'assigned_at'     => now(),
                'status'          => 'pending',
            ];

            if ($file) {
                $existing = \App\Models\HomeworkSubmission::where('lesson_id', $lesson->id)
                    ->where('student_id', $studentId)->first();
                if ($existing?->assignment_file_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($existing->assignment_file_path);
                }
                $updateData['assignment_file_path'] = $file->store('homework/assignments', 'public');
            }

            \App\Models\HomeworkSubmission::updateOrCreate(
                ['lesson_id' => $lesson->id, 'student_id' => $studentId],
                $updateData
            );
        }

        return redirect()->route('teacher.lessons.homework.edit', $lesson)
            ->with('success', 'Домашні завдання збережено.');
    }
}
