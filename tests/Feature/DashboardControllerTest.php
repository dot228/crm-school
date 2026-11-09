<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('dashboard.index');
    }

    public function test_manager_sees_dashboard(): void
    {
        $manager = User::factory()->manager()->create();

        $this->actingAs($manager)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('dashboard.index');
    }

    public function test_teacher_redirected_from_dashboard_to_students(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('dashboard'))
            ->assertRedirect(route('teacher.students'));
    }

    public function test_student_redirected_from_dashboard_to_schedule(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertRedirect(route('student.schedule'));
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
