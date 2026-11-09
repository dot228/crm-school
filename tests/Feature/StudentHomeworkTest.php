<?php

namespace Tests\Feature;

use App\Models\HomeworkSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentHomeworkTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_homework_list(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('student.homework'))
            ->assertOk()
            ->assertViewIs('student.homework');
    }

    public function test_student_sees_own_homework(): void
    {
        $student = User::factory()->student()->create();
        $hw = HomeworkSubmission::factory()->create(['student_id' => $student->id]);

        $response = $this->actingAs($student)->get(route('student.homework'));

        $submissions = $response->viewData('submissions');
        $this->assertTrue($submissions->contains('id', $hw->id));
    }

    public function test_student_cannot_see_other_students_homework(): void
    {
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();
        HomeworkSubmission::factory()->create(['student_id' => $student2->id]);

        $response = $this->actingAs($student1)->get(route('student.homework'));

        $submissions = $response->viewData('submissions');
        $this->assertTrue($submissions->isEmpty());
    }

    public function test_student_can_view_homework_show(): void
    {
        $student = User::factory()->student()->create();
        $hw = HomeworkSubmission::factory()->create([
            'student_id'      => $student->id,
            'assignment_text' => 'Do exercise 1',
        ]);

        $this->actingAs($student)
            ->get(route('student.homework.show', $hw))
            ->assertOk()
            ->assertViewIs('student.homework-show');
    }

    public function test_student_cannot_view_others_homework_show(): void
    {
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();
        $hw = HomeworkSubmission::factory()->create(['student_id' => $student2->id]);

        $this->actingAs($student1)
            ->get(route('student.homework.show', $hw))
            ->assertForbidden();
    }

    public function test_student_can_submit_homework_with_content(): void
    {
        $student = User::factory()->student()->create();
        $hw = HomeworkSubmission::factory()->create([
            'student_id' => $student->id,
            'status'     => 'pending',
        ]);

        $this->actingAs($student)
            ->post(route('student.homework.submit', $hw), ['content' => 'My answer'])
            ->assertRedirect(route('student.homework'));

        $this->assertEquals('submitted', $hw->fresh()->status);
        $this->assertEquals('My answer', $hw->fresh()->content);
    }

    public function test_student_can_submit_homework_with_file(): void
    {
        Storage::fake('public');
        $student = User::factory()->student()->create();
        $hw = HomeworkSubmission::factory()->create([
            'student_id' => $student->id,
            'status'     => 'pending',
        ]);

        $this->actingAs($student)
            ->post(route('student.homework.submit', $hw), [
                'file' => UploadedFile::fake()->create('homework.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('student.homework'));

        $this->assertNotNull($hw->fresh()->file_path);
    }

    public function test_student_cannot_submit_reviewed_homework(): void
    {
        $student = User::factory()->student()->create();
        $hw = HomeworkSubmission::factory()->reviewed()->create(['student_id' => $student->id]);

        $this->actingAs($student)
            ->post(route('student.homework.submit', $hw), ['content' => 'Late answer'])
            ->assertForbidden();
    }

    public function test_non_student_cannot_submit_homework(): void
    {
        $teacher = User::factory()->teacher()->create();
        $hw = HomeworkSubmission::factory()->create();

        $this->actingAs($teacher)
            ->post(route('student.homework.submit', $hw), ['content' => 'x'])
            ->assertForbidden();
    }
}
