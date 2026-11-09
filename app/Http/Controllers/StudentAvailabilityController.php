<?php

namespace App\Http\Controllers;

use App\Models\TeacherAvailability;
use Illuminate\Http\Request;

class StudentAvailabilityController extends Controller
{
    public function index()
    {
        $student        = auth()->user();
        $availabilities = TeacherAvailability::where('teacher_id', $student->id)
            ->get()
            ->groupBy('day_of_week');
        $days = TeacherAvailability::DAYS;

        return view('student.availability', compact('availabilities', 'days'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slots'                => 'nullable|array',
            'slots.*'              => 'array',
            'slots.*.*.start_time' => 'required|date_format:H:i',
            'slots.*.*.end_time'   => 'required|date_format:H:i',
        ]);

        $studentId = auth()->id();
        $slots     = $request->input('slots', []);

        TeacherAvailability::where('teacher_id', $studentId)->delete();

        foreach ($slots as $dayNum => $daySlots) {
            foreach ($daySlots as $slot) {
                $start = $slot['start_time'] ?? null;
                $end   = $slot['end_time']   ?? null;
                if ($start && $end && $start < $end) {
                    TeacherAvailability::create([
                        'teacher_id'  => $studentId,
                        'day_of_week' => (int) $dayNum,
                        'start_time'  => $start,
                        'end_time'    => $end,
                        'is_active'   => true,
                    ]);
                }
            }
        }

        return redirect()->route('student.availability')
            ->with('success', 'Розклад вільних годин збережено.');
    }
}
