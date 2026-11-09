<?php

namespace Tests\Feature;

use App\Models\TeacherAvailability;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_view_availability_page(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('teacher.availability'))
            ->assertOk()
            ->assertViewIs('teacher.availability');
    }

    public function test_non_teacher_cannot_access_availability(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('teacher.availability'))
            ->assertForbidden();
    }

    public function test_teacher_can_store_availability(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('teacher.availability.store'), [
                'slots' => [
                    1 => [['start_time' => '09:00', 'end_time' => '18:00']],
                    5 => [
                        ['start_time' => '10:00', 'end_time' => '13:00'],
                        ['start_time' => '14:00', 'end_time' => '17:00'],
                    ],
                ],
            ])
            ->assertRedirect(route('teacher.availability'))
            ->assertSessionHas('success');

        $this->assertEquals(3, TeacherAvailability::where('teacher_id', $teacher->id)->count());
    }

    public function test_store_skips_slots_where_end_not_after_start(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->post(route('teacher.availability.store'), [
                'slots' => [
                    1 => [['start_time' => '17:00', 'end_time' => '09:00']],
                ],
            ]);

        $this->assertEquals(0, TeacherAvailability::where('teacher_id', $teacher->id)->count());
    }

    public function test_store_replaces_all_existing_slots(): void
    {
        $teacher = User::factory()->teacher()->create();
        TeacherAvailability::factory()->count(3)->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher)
            ->post(route('teacher.availability.store'), [
                'slots' => [
                    2 => [['start_time' => '08:00', 'end_time' => '12:00']],
                ],
            ]);

        $this->assertEquals(1, TeacherAvailability::where('teacher_id', $teacher->id)->count());
    }

    public function test_view_passes_days_and_availabilities(): void
    {
        $teacher = User::factory()->teacher()->create();

        $response = $this->actingAs($teacher)->get(route('teacher.availability'));

        $response->assertViewHas('days');
        $response->assertViewHas('availabilities');
    }
}
