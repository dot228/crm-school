<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_helpers(): void
    {
        $admin   = User::factory()->admin()->make();
        $manager = User::factory()->manager()->make();
        $teacher = User::factory()->teacher()->make();
        $student = User::factory()->student()->make();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isTeacher());

        $this->assertTrue($manager->isManager());
        $this->assertFalse($manager->isAdmin());

        $this->assertTrue($teacher->isTeacher());
        $this->assertFalse($teacher->isStudent());

        $this->assertTrue($student->isStudent());
        $this->assertFalse($student->isTeacher());
    }

    public function test_role_label_attribute(): void
    {
        $this->assertEquals('Admin',   User::factory()->admin()->make()->role_label);
        $this->assertEquals('Teacher', User::factory()->teacher()->make()->role_label);
        $this->assertEquals('Student', User::factory()->student()->make()->role_label);
    }

    public function test_avatar_url_returns_ui_avatars_when_no_avatar(): void
    {
        $user = User::factory()->make(['name' => 'John Doe', 'avatar' => null]);

        $this->assertStringContainsString('ui-avatars.com', $user->avatar_url);
        $this->assertStringContainsString('John', $user->avatar_url);
    }

    public function test_age_attribute_returns_null_when_no_birth_date(): void
    {
        $user = User::factory()->make(['date_of_birth' => null]);
        $this->assertNull($user->age);
    }

    public function test_age_attribute_calculated_correctly(): void
    {
        $user = User::factory()->make([
            'date_of_birth' => now()->subYears(20)->toDateString(),
        ]);

        $this->assertEquals(20, $user->age);
    }

    public function test_is_adult_returns_true_when_no_birth_date(): void
    {
        $user = User::factory()->make(['date_of_birth' => null]);
        $this->assertTrue($user->isAdult());
    }

    public function test_two_factor_not_enabled_by_default(): void
    {
        $user = User::factory()->make(['two_factor_confirmed_at' => null]);
        $this->assertFalse($user->hasTwoFactorEnabled());
    }
}
