<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Schedule;
use App\Models\TeacherAvailability;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateDraftSchedulesCommandTest extends TestCase
{
    use RefreshDatabase;

    private function makeEnrollmentWithAvailability(
        string $teacherStart,
        string $teacherEnd,
        string $studentStart,
        string $studentEnd,
        int    $dow = null
    ): array {
        // Use tomorrow's day-of-week so we always get a hit
        $dow     = $dow ?? Carbon::tomorrow()->isoWeekday();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $course  = Course::factory()->create(['teacher_id' => $teacher->id]);
        $group   = Group::factory()->create(['teacher_id' => $teacher->id, 'course_id' => $course->id]);
        $lesson  = Lesson::factory()->create(['course_id' => $course->id, 'order' => 1, 'duration_minutes' => 60]);
        $enr     = Enrollment::factory()->create(['student_id' => $student->id, 'group_id' => $group->id]);

        TeacherAvailability::factory()->create([
            'teacher_id'  => $teacher->id,
            'day_of_week' => $dow,
            'start_time'  => $teacherStart,
            'end_time'    => $teacherEnd,
            'is_active'   => true,
        ]);

        TeacherAvailability::factory()->create([
            'teacher_id'  => $student->id,
            'day_of_week' => $dow,
            'start_time'  => $studentStart,
            'end_time'    => $studentEnd,
            'is_active'   => true,
        ]);

        return compact('teacher', 'student', 'course', 'lesson', 'enr');
    }

    public function test_command_creates_draft_when_availabilities_overlap(): void
    {
        $this->makeEnrollmentWithAvailability('09:00', '18:00', '10:00', '14:00');

        $this->artisan('schedules:generate-drafts --days=1')
            ->assertSuccessful()
            ->expectsOutputToContain('Created: 1');

        $this->assertEquals(1, Schedule::where('status', 'draft')->count());
    }

    public function test_draft_schedule_has_correct_attributes(): void
    {
        $ctx = $this->makeEnrollmentWithAvailability('09:00', '18:00', '10:00', '14:00');

        $this->artisan('schedules:generate-drafts --days=1');

        $schedule = Schedule::where('status', 'draft')->first();
        $this->assertNotNull($schedule);
        $this->assertEquals($ctx['student']->id, $schedule->student_id);
        $this->assertEquals($ctx['teacher']->id, $schedule->teacher_id);
        $this->assertEquals($ctx['lesson']->id,  $schedule->lesson_id);
        $this->assertEquals('10:00', substr($schedule->scheduled_time, 0, 5));
        $this->assertEquals(60, $schedule->duration_minutes);
    }

    public function test_command_skips_when_no_overlap(): void
    {
        // Teacher available 09-12, student available 13-17 → no overlap
        $this->makeEnrollmentWithAvailability('09:00', '12:00', '13:00', '17:00');

        $this->artisan('schedules:generate-drafts --days=1')
            ->expectsOutputToContain('Created: 0');

        $this->assertEquals(0, Schedule::where('status', 'draft')->count());
    }

    public function test_command_skips_when_teacher_has_no_availability(): void
    {
        $student = User::factory()->student()->create();
        $teacher = User::factory()->teacher()->create();
        $course  = Course::factory()->create(['teacher_id' => $teacher->id]);
        $group   = Group::factory()->create(['teacher_id' => $teacher->id, 'course_id' => $course->id]);
        Lesson::factory()->create(['course_id' => $course->id, 'order' => 1]);
        Enrollment::factory()->create(['student_id' => $student->id, 'group_id' => $group->id]);

        // Only student has availability, teacher has none
        TeacherAvailability::factory()->create([
            'teacher_id'  => $student->id,
            'day_of_week' => Carbon::tomorrow()->isoWeekday(),
            'start_time'  => '10:00',
            'end_time'    => '14:00',
            'is_active'   => true,
        ]);

        $this->artisan('schedules:generate-drafts --days=1')
            ->expectsOutputToContain('Created: 0');
    }

    public function test_command_does_not_duplicate_existing_schedules(): void
    {
        $ctx = $this->makeEnrollmentWithAvailability('09:00', '18:00', '10:00', '14:00');

        // First run
        $this->artisan('schedules:generate-drafts --days=1');
        $this->assertEquals(1, Schedule::where('status', 'draft')->count());

        // Second run should skip existing
        $this->artisan('schedules:generate-drafts --days=1')
            ->expectsOutputToContain('Skipped');

        $this->assertEquals(1, Schedule::where('status', 'draft')->count());
    }

    public function test_command_skips_when_no_lessons_in_course(): void
    {
        $dow     = Carbon::tomorrow()->isoWeekday();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $course  = Course::factory()->create(['teacher_id' => $teacher->id]);
        $group   = Group::factory()->create(['teacher_id' => $teacher->id, 'course_id' => $course->id]);
        // No lessons created
        Enrollment::factory()->create(['student_id' => $student->id, 'group_id' => $group->id]);

        TeacherAvailability::factory()->create(['teacher_id' => $teacher->id, 'day_of_week' => $dow, 'start_time' => '09:00', 'end_time' => '18:00', 'is_active' => true]);
        TeacherAvailability::factory()->create(['teacher_id' => $student->id, 'day_of_week' => $dow, 'start_time' => '09:00', 'end_time' => '18:00', 'is_active' => true]);

        $this->artisan('schedules:generate-drafts --days=1')
            ->expectsOutputToContain('Created: 0');
    }

    public function test_command_respects_days_option(): void
    {
        $this->makeEnrollmentWithAvailability('09:00', '18:00', '10:00', '14:00');

        // days=0 means no future dates to check
        $this->artisan('schedules:generate-drafts --days=0')
            ->expectsOutputToContain('Created: 0');
    }

    public function test_command_skips_inactive_enrollments(): void
    {
        $dow     = Carbon::tomorrow()->isoWeekday();
        $teacher = User::factory()->teacher()->create();
        $student = User::factory()->student()->create();
        $course  = Course::factory()->create(['teacher_id' => $teacher->id]);
        $group   = Group::factory()->create(['teacher_id' => $teacher->id, 'course_id' => $course->id]);
        Lesson::factory()->create(['course_id' => $course->id, 'order' => 1]);
        Enrollment::factory()->create(['student_id' => $student->id, 'group_id' => $group->id, 'status' => 'cancelled']);

        TeacherAvailability::factory()->create(['teacher_id' => $teacher->id, 'day_of_week' => $dow, 'start_time' => '09:00', 'end_time' => '18:00', 'is_active' => true]);
        TeacherAvailability::factory()->create(['teacher_id' => $student->id, 'day_of_week' => $dow, 'start_time' => '09:00', 'end_time' => '18:00', 'is_active' => true]);

        $this->artisan('schedules:generate-drafts --days=1')
            ->expectsOutputToContain('Created: 0');
    }
}
