<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\TeacherAvailability;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class GenerateDraftSchedules extends Command
{
    protected $signature   = 'schedules:generate-drafts {--days=14 : How many days ahead to generate}';
    protected $description = 'Auto-generate draft schedules based on teacher and student availability overlaps';

    public function handle(): int
    {
        $days     = (int) $this->option('days');
        $today    = Carbon::today();
        $horizon  = $today->copy()->addDays($days);
        $created  = 0;
        $skipped  = 0;

        $enrollments = Enrollment::with(['group.course', 'group.teacher'])
            ->where('status', 'active')
            ->get();

        foreach ($enrollments as $enrollment) {
            $group = $enrollment->group;
            if (!$group || !$group->teacher_id || !$group->course_id) {
                continue;
            }

            $teacherId = $group->teacher_id;
            $studentId = $enrollment->student_id;
            $courseId  = $group->course_id;

            // Load availabilities indexed by day_of_week
            $teacherAvail = TeacherAvailability::where('teacher_id', $teacherId)
                ->where('is_active', true)
                ->get()
                ->groupBy('day_of_week');

            $studentAvail = TeacherAvailability::where('teacher_id', $studentId)
                ->where('is_active', true)
                ->get()
                ->groupBy('day_of_week');

            // Skip if either side has no availability set
            if ($teacherAvail->isEmpty() || $studentAvail->isEmpty()) {
                continue;
            }

            // Iterate over each future date in the horizon
            for ($date = $today->copy()->addDay(); $date->lte($horizon); $date->addDay()) {
                $dow = $date->isoWeekday(); // 1=Mon … 7=Sun

                $tSlots = $teacherAvail->get($dow, collect());
                $sSlots = $studentAvail->get($dow, collect());

                if ($tSlots->isEmpty() || $sSlots->isEmpty()) {
                    continue;
                }

                $overlaps = $this->findOverlaps($tSlots, $sSlots);

                foreach ($overlaps as $overlapStart) {
                    // Avoid duplicate schedule for same student/date/time
                    $exists = Schedule::where('student_id', $studentId)
                        ->where('scheduled_date', $date->toDateString())
                        ->where('scheduled_time', $overlapStart)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    // Find next lesson for this student in this course
                    $usedLessonIds = Schedule::where('student_id', $studentId)
                        ->whereHas('lesson', fn($q) => $q->where('course_id', $courseId))
                        ->whereIn('status', ['scheduled', 'completed', 'draft'])
                        ->pluck('lesson_id');

                    $nextLesson = Lesson::where('course_id', $courseId)
                        ->whereNotIn('id', $usedLessonIds)
                        ->orderBy('order')
                        ->first();

                    if (!$nextLesson) {
                        break; // No more lessons in course for this student
                    }

                    Schedule::create([
                        'lesson_id'        => $nextLesson->id,
                        'student_id'       => $studentId,
                        'teacher_id'       => $teacherId,
                        'scheduled_date'   => $date->toDateString(),
                        'scheduled_time'   => $overlapStart,
                        'duration_minutes' => $nextLesson->duration_minutes ?? 60,
                        'status'           => 'draft',
                        'notes'            => 'Автоматично створено на основі вільних годин',
                    ]);

                    $created++;
                }
            }
        }

        $this->info("Done. Created: {$created}, Skipped (already exist): {$skipped}");
        return self::SUCCESS;
    }

    /**
     * Find the start times of all overlapping intervals between teacher and student slots.
     * Returns an array of time strings "HH:MM" representing overlap start times.
     */
    private function findOverlaps(Collection $teacherSlots, Collection $studentSlots): array
    {
        $overlaps = [];

        foreach ($teacherSlots as $t) {
            foreach ($studentSlots as $s) {
                $start = max($t->start_time, $s->start_time);
                $end   = min($t->end_time,   $s->end_time);

                if ($start < $end) {
                    // Use the start of overlap as the lesson time
                    $overlaps[] = substr($start, 0, 5); // "HH:MM"
                }
            }
        }

        return array_unique($overlaps);
    }
}
