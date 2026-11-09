<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonContentController extends Controller
{
    public function create(Lesson $lesson)
    {
        return view('lesson-contents.create', compact('lesson'));
    }

    public function store(Request $request, Lesson $lesson)
    {
        $data = $request->validate([
            'type'         => 'required|in:text,video,file,link',
            'title'        => 'required|string|max:255',
            'body'         => 'nullable|string',
            'url'          => 'nullable|url|max:500',
            'file'         => 'nullable|file|max:20480',
            'order'        => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('lesson-files', 'public');
        }

        $data['lesson_id']    = $lesson->id;
        $data['is_published'] = $request->boolean('is_published', true);
        unset($data['file']);

        LessonContent::create($data);

        return redirect()->route('lessons.show', $lesson)
            ->with('success', 'Контент уроку додано.');
    }

    public function edit(Lesson $lesson, LessonContent $lessonContent)
    {
        return view('lesson-contents.edit', compact('lesson', 'lessonContent'));
    }

    public function update(Request $request, Lesson $lesson, LessonContent $lessonContent)
    {
        $data = $request->validate([
            'type'         => 'required|in:text,video,file,link',
            'title'        => 'required|string|max:255',
            'body'         => 'nullable|string',
            'url'          => 'nullable|url|max:500',
            'file'         => 'nullable|file|max:20480',
            'order'        => 'nullable|integer|min:0',
            'is_published' => 'nullable|boolean',
        ]);

        if ($request->hasFile('file')) {
            if ($lessonContent->file_path) {
                Storage::disk('public')->delete($lessonContent->file_path);
            }
            $data['file_path'] = $request->file('file')->store('lesson-files', 'public');
        }

        $data['is_published'] = $request->boolean('is_published', true);
        unset($data['file']);

        $lessonContent->update($data);

        return redirect()->route('lessons.show', $lesson)
            ->with('success', 'Контент оновлено.');
    }

    public function destroy(Lesson $lesson, LessonContent $lessonContent)
    {
        if ($lessonContent->file_path) {
            Storage::disk('public')->delete($lessonContent->file_path);
        }

        $lessonContent->delete();

        return redirect()->route('lessons.show', $lesson)
            ->with('success', 'Контент видалено.');
    }
}
