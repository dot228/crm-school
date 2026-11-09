<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\TariffPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPaymentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_payments_page(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->get(route('student.payments'))
            ->assertOk()
            ->assertViewIs('student.payments');
    }

    public function test_non_student_cannot_access_payments_page(): void
    {
        $teacher = User::factory()->teacher()->create();

        $this->actingAs($teacher)
            ->get(route('student.payments'))
            ->assertForbidden();
    }

    public function test_student_can_submit_tariff_payment_request(): void
    {
        $student    = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['student_id' => $student->id]);
        $tariff     = TariffPlan::factory()->create(['status' => 'active', 'count_lessons' => 8, 'amount' => 1000]);

        $this->actingAs($student)
            ->post(route('student.payments.pay'), [
                'enrollment_id'  => $enrollment->id,
                'tariff_plan_id' => $tariff->id,
            ])
            ->assertRedirect(route('student.payments'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'student_id'    => $student->id,
            'enrollment_id' => $enrollment->id,
            'status'        => 'pending',
            'amount'        => 1000,
        ]);
    }

    public function test_student_cannot_pay_for_other_students_enrollment(): void
    {
        $student1   = User::factory()->student()->create();
        $student2   = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['student_id' => $student2->id]);
        $tariff     = TariffPlan::factory()->create(['status' => 'active']);

        $this->actingAs($student1)
            ->post(route('student.payments.pay'), [
                'enrollment_id'  => $enrollment->id,
                'tariff_plan_id' => $tariff->id,
            ])
            ->assertStatus(404);
    }

    public function test_student_can_topup_money_balance(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('student.payments.topup'), [
                'amount' => 500,
                'method' => 'cash',
            ])
            ->assertRedirect(route('student.payments'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'student_id'   => $student->id,
            'payment_type' => 'money',
            'amount'       => 500,
            'status'       => 'pending',
            'method'       => 'cash',
        ]);
    }

    public function test_topup_money_validates_minimum_amount(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('student.payments.topup'), ['amount' => 5, 'method' => 'cash'])
            ->assertSessionHasErrors('amount');
    }

    public function test_topup_money_validates_method(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('student.payments.topup'), ['amount' => 100, 'method' => 'bitcoin'])
            ->assertSessionHasErrors('method');
    }

    public function test_payments_page_shows_money_balance(): void
    {
        $student = User::factory()->student()->create(['money_balance' => 350.00]);

        $response = $this->actingAs($student)->get(route('student.payments'));

        $this->assertEquals(350.00, (float) $response->viewData('moneyBalance'));
    }

    public function test_payments_page_lists_only_own_payments(): void
    {
        $student1 = User::factory()->student()->create();
        $student2 = User::factory()->student()->create();
        $payment  = Payment::factory()->create(['student_id' => $student1->id]);
        Payment::factory()->create(['student_id' => $student2->id]);

        $response = $this->actingAs($student1)->get(route('student.payments'));

        $payments = $response->viewData('payments');
        $this->assertTrue($payments->contains('id', $payment->id));
        $this->assertEquals(1, $payments->count());
    }
}
