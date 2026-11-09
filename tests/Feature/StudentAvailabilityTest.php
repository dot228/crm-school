<?php

namespace Tests\Feature;

use App\Models\TeacherAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_availability_page(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('student.availability'))
            ->assertOk()
            ->assertViewIs('student.availability');
    }

    public function test_non_student_cannot_access_availability_page(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('student.availability'))
            ->assertForbidden();
    }

    public function test_student_can_store_availability_slots(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('student.availability.store'), [
                'slots' => [
                    1 => [['start_time' => '09:00', 'end_time' => '12:00']],
                    3 => [['start_time' => '14:00', 'end_time' => '17:00']],
                ],
            ])
            ->assertRedirect(route('student.availability'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('teacher_availabilities', [
            'teacher_id'  => $student->id,
            'day_of_week' => 1,
            'start_time'  => '09:00:00',
            'end_time'    => '12:00:00',
        ]);

        $this->assertDatabaseHas('teacher_availabilities', [
            'teacher_id'  => $student->id,
            'day_of_week' => 3,
        ]);
    }

    public function test_store_replaces_existing_slots(): void
    {
        $student = User::factory()->student()->create();

        TeacherAvailability::factory()->create([
            'teacher_id'  => $student->id,
            'day_of_week' => 1,
            'start_time'  => '08:00:00',
            'end_time'    => '10:00:00',
        ]);

        $this->actingAs($student)
            ->post(route('student.availability.store'), [
                'slots' => [
                    1 => [['start_time' => '10:00', 'end_time' => '14:00']],
                ],
            ]);

        $slots = TeacherAvailability::where('teacher_id', $student->id)->get();
        $this->assertCount(1, $slots);
        $this->assertEquals('10:00:00', $slots->first()->start_time);
    }

    public function test_store_clears_all_slots_when_none_submitted(): void
    {
        $student = User::factory()->student()->create();

        TeacherAvailability::factory()->create(['teacher_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('student.availability.store'), []);

        $this->assertDatabaseMissing('teacher_availabilities', ['teacher_id' => $student->id]);
    }

    public function test_store_rejects_invalid_time_format(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('student.availability.store'), [
                'slots' => [1 => [['start_time' => 'bad', 'end_time' => '17:00']]],
            ])
            ->assertSessionHasErrors();
    }

    public function test_view_shows_existing_availability(): void
    {
        $student = User::factory()->student()->create();
        TeacherAvailability::factory()->create(['teacher_id' => $student->id, 'day_of_week' => 2]);

        $response = $this->actingAs($student)->get(route('student.availability'));

        $availabilities = $response->viewData('availabilities');
        $this->assertTrue($availabilities->has(2));
    }
}
