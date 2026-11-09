<?php

namespace Tests\Unit;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Schedule;
use App\Models\User;
use App\Services\LessonBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private LessonBalanceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LessonBalanceService();
    }

    public function test_credit_increments_lessons_balance(): void
    {
        $enrollment = Enrollment::factory()->create(['lessons_balance' => 5]);

        $this->service->credit($enrollment, 3);

        $this->assertEquals(8, $enrollment->fresh()->lessons_balance);
    }

    public function test_credit_money_increments_user_money_balance(): void
    {
        $student = User::factory()->student()->create(['money_balance' => 100]);

        $this->service->creditMoney($student->id, 250.50);

        $this->assertEquals(350.50, (float) $student->fresh()->money_balance);
    }

    public function test_credit_from_payment_money_type_credits_money_balance(): void
    {
        $student = User::factory()->student()->create(['money_balance' => 0]);

        $payment = Payment::factory()->money()->create([
            'student_id' => $student->id,
            'amount'     => 500,
            'status'     => 'paid',
        ]);

        $this->service->creditFromPayment($payment);

        $this->assertEquals(500, (float) $student->fresh()->money_balance);
    }

    public function test_credit_from_payment_lessons_type_credits_lessons_balance(): void
    {
        $enrollment = Enrollment::factory()->create(['lessons_balance' => 0]);
        $tariff     = \App\Models\TariffPlan::factory()->create(['count_lessons' => 8, 'amount' => 1000]);

        $payment = Payment::factory()->create([
            'student_id'     => $enrollment->student_id,
            'enrollment_id'  => $enrollment->id,
            'tariff_plan_id' => $tariff->id,
            'lessons_count'  => 0,
            'payment_type'   => 'lessons',
            'status'         => 'paid',
        ]);

        $this->service->creditFromPayment($payment->fresh(['tariffPlan', 'enrollment']));

        $this->assertEquals(8, $enrollment->fresh()->lessons_balance);
    }

    public function test_credit_from_payment_uses_lessons_count_over_tariff(): void
    {
        $enrollment = Enrollment::factory()->create(['lessons_balance' => 0]);
        $tariff     = \App\Models\TariffPlan::factory()->create(['count_lessons' => 8]);

        $payment = Payment::factory()->create([
            'student_id'     => $enrollment->student_id,
            'enrollment_id'  => $enrollment->id,
            'tariff_plan_id' => $tariff->id,
            'lessons_count'  => 5,
            'payment_type'   => 'lessons',
        ]);

        $this->service->creditFromPayment($payment->fresh(['tariffPlan', 'enrollment']));

        $this->assertEquals(5, $enrollment->fresh()->lessons_balance);
    }

    public function test_credit_from_payment_skips_when_no_enrollment_id_and_not_money(): void
    {
        $student = User::factory()->student()->create(['money_balance' => 0]);

        $payment = Payment::factory()->create([
            'student_id'    => $student->id,
            'enrollment_id' => null,
            'payment_type'  => 'lessons',
        ]);

        $this->service->creditFromPayment($payment);

        $this->assertEquals(0, (float) $student->fresh()->money_balance);
    }
}
