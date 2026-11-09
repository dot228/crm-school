<?php

namespace App\Http\Controllers;

use App\Models\TeacherAvailability;
use Illuminate\Http\Request;

class TeacherAvailabilityController extends Controller
{
    public function index()
    {
        $teacher = auth()->user();
        $availabilities = $teacher->availabilities->groupBy('day_of_week');
        $days = TeacherAvailability::DAYS;

        return view('teacher.availability', compact('availabilities', 'days'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slots'                   => 'nullable|array',
            'slots.*'                 => 'array',
            'slots.*.*.start_time'    => 'required|date_format:H:i',
            'slots.*.*.end_time'      => 'required|date_format:H:i',
        ]);

        $teacherId = auth()->id();
        $slots     = $request->input('slots', []);

        TeacherAvailability::where('teacher_id', $teacherId)->delete();

        foreach ($slots as $dayNum => $daySlots) {
            foreach ($daySlots as $slot) {
                $start = $slot['start_time'] ?? null;
                $end   = $slot['end_time']   ?? null;
                if ($start && $end && $start < $end) {
                    TeacherAvailability::create([
                        'teacher_id'  => $teacherId,
                        'day_of_week' => (int) $dayNum,
                        'start_time'  => $start,
                        'end_time'    => $end,
                        'is_active'   => true,
                    ]);
                }
            }
        }

        return redirect()->route('teacher.availability')
            ->with('success', 'Розклад доступності збережено.');
    }
}
