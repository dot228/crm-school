<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_see_schedule_page(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('student.schedule'))
            ->assertOk()
            ->assertViewIs('student.schedule');
    }

    public function test_student_sees_own_scheduled_entries(): void
    {
        $student  = User::factory()->student()->create();
        $schedule = Schedule::factory()->create(['student_id' => $student->id, 'status' => 'scheduled']);

        $response = $this->actingAs($student)->get(route('student.schedule'));

        $response->assertOk();
        $schedules = $response->viewData('schedules');
        $this->assertTrue($schedules->contains('id', $schedule->id));
    }

    public function test_student_does_not_see_draft_schedules(): void
    {
        $student = User::factory()->student()->create();
        Schedule::factory()->draft()->create(['student_id' => $student->id]);

        $response = $this->actingAs($student)->get(route('student.schedule'));

        $schedules = $response->viewData('schedules');
        $this->assertTrue($schedules->where('status', 'draft')->isEmpty());
    }

    public function test_student_does_not_see_other_students_schedules(): void
    {
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();
        Schedule::factory()->create(['student_id' => $student2->id, 'status' => 'scheduled']);

        $response = $this->actingAs($student1)->get(route('student.schedule'));

        $schedules = $response->viewData('schedules');
        $this->assertTrue($schedules->isEmpty());
    }

    public function test_non_student_cannot_access_student_schedule(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('student.schedule'))
            ->assertForbidden();
    }

    public function test_student_schedule_calendar_month_view(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('student.schedule', ['view' => 'calendar', 'cal_period' => 'month']))
            ->assertOk()
            ->assertViewHas('calSchedules');
    }

    public function test_student_schedule_calendar_week_view(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('student.schedule', ['view' => 'calendar', 'cal_period' => 'week']))
            ->assertOk()
            ->assertViewHas('weekStart');
    }

    public function test_student_schedule_calendar_day_view(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('student.schedule', ['view' => 'calendar', 'cal_period' => 'day']))
            ->assertOk()
            ->assertViewHas('calDate');
    }
}
