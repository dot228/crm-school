<?php

namespace App\Http\Controllers;

use App\Models\HomeworkSubmission;
use App\Models\Lesson;
use Illuminate\Http\Request;

class HomeworkSubmissionController extends Controller
{
    private function teacherLessonIds()
    {
        $courseIds = \App\Models\Course::where('teacher_id', auth()->id())
            ->orWhereHas('groups', fn($q) => $q->where('teacher_id', auth()->id()))
            ->pluck('id');
        return Lesson::whereIn('course_id', $courseIds)->pluck('id');
    }

    public function index(Request $request)
    {
        $lessonIds = $this->teacherLessonIds();

        $query = HomeworkSubmission::with(['lesson.course', 'student', 'schedule'])
            ->whereIn('lesson_id', $lessonIds)
            ->orderByRaw("FIELD(status, 'submitted', 'pending', 'reviewed')")
            ->orderBy('submitted_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('lesson_id')) {
            $query->where('lesson_id', $request->lesson_id);
        }

        $submissions = $query->paginate(20);
        $lessons     = Lesson::with('course')->whereIn('id', $lessonIds)->orderBy('title')->get();

        return view('teacher.homework.index', compact('submissions', 'lessons'));
    }

    public function show(HomeworkSubmission $homeworkSubmission)
    {
        $lessonIds = $this->teacherLessonIds();
        abort_unless($lessonIds->contains($homeworkSubmission->lesson_id), 403);

        $homeworkSubmission->load(['lesson.course', 'student', 'schedule']);
        return view('teacher.homework.show', compact('homeworkSubmission'));
    }

    public function review(Request $request, HomeworkSubmission $homeworkSubmission)
    {
        $lessonIds = $this->teacherLessonIds();
        abort_unless($lessonIds->contains($homeworkSubmission->lesson_id), 403);

        $request->validate([
            'grade'           => 'nullable|in:A,B,C,D,F',
            'teacher_comment' => 'nullable|string|max:2000',
            'teacher_file'    => 'nullable|file|max:10240|mimes:pdf,doc,docx,txt,zip,png,jpg,jpeg',
        ]);

        $updateData = [
            'grade'           => $request->grade,
            'teacher_comment' => $request->teacher_comment,
            'status'          => 'reviewed',
            'reviewed_at'     => now(),
        ];

        if ($request->hasFile('teacher_file')) {
            if ($homeworkSubmission->teacher_file_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($homeworkSubmission->teacher_file_path);
            }
            $updateData['teacher_file_path'] = $request->file('teacher_file')->store('homework/teacher', 'public');
        }

        $homeworkSubmission->update($updateData);

        return redirect()->route('teacher.homework.index')
            ->with('success', 'Домашнє завдання перевірено.');
    }
}
