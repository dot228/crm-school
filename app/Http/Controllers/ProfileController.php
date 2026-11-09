<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load([
            'enrollments.group.course',
            'payments',
            'teachingGroups.course',
        ]);

        $schedules = \App\Models\Schedule::with(['lesson.course', 'student', 'teacher'])
            ->when($user->isStudent(), fn($q) => $q->where('student_id', $user->id))
            ->when($user->isTeacher(), fn($q) => $q->where('teacher_id', $user->id))
            ->orderBy('scheduled_date', 'desc')
            ->orderBy('scheduled_time', 'desc')
            ->limit(10)
            ->get();

        return view('profile.show', compact('user', 'schedules'));
    }

    public function edit()
    {
        $user = auth()->user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'bio'              => ['nullable', 'string'],
            'avatar_file'      => ['nullable', 'image', 'max:2048'],
            'password'         => ['nullable', 'string', 'min:6', 'confirmed'],
            'date_of_birth'    => ['nullable', 'date', 'before:today'],
            'guardian_name'    => ['nullable', 'string', 'max:255'],
            'guardian_phone'   => ['nullable', 'string', 'max:20'],
            'guardian_email'   => ['nullable', 'email', 'max:255'],
            'guardian_relation'=> ['nullable', 'string', 'max:100'],
        ]);

        if ($request->hasFile('avatar_file')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar_file')->store('avatars', 'public');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['avatar_file']);
        $user->update($data);

        return redirect()->route('profile.show')
            ->with('success', 'Профіль оновлено успішно.');
    }
}
