<?php

namespace Tests\Feature;

use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\TariffPlan;
use App\Models\User;
use App\Services\WayForPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WayForPayControllerTest extends TestCase
{
    use RefreshDatabase;

    private function configureWfp(): void
    {
        Setting::set('wfp_merchant_account', 'test_merchant');
        Setting::set('wfp_merchant_secret',  'test_secret');
        Setting::set('wfp_merchant_domain',  'example.com');
    }

    public function test_initiate_requires_enrollment_and_tariff(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('wayforpay.initiate'), [])
            ->assertSessionHasErrors(['enrollment_id', 'tariff_plan_id']);
    }

    public function test_initiate_creates_pending_payment_and_returns_redirect_view(): void
    {
        $this->configureWfp();

        $student    = User::factory()->student()->create();
        $enrollment = Enrollment::factory()->create(['student_id' => $student->id]);
        $tariff     = TariffPlan::factory()->create(['status' => 'active', 'count_lessons' => 4, 'amount' => 800]);

        $response = $this->actingAs($student)
            ->post(route('wayforpay.initiate'), [
                'enrollment_id'  => $enrollment->id,
                'tariff_plan_id' => $tariff->id,
            ]);

        $response->assertOk()->assertViewIs('wayforpay.redirect');

        $this->assertDatabaseHas('payments', [
            'student_id'    => $student->id,
            'enrollment_id' => $enrollment->id,
            'status'        => 'pending',
            'method'        => 'online',
            'amount'        => 800,
        ]);
    }

    public function test_initiate_money_topup_redirects_when_wfp_not_configured(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('wayforpay.money'), ['amount' => 300])
            ->assertRedirect(route('student.payments'))
            ->assertSessionHas('error');
    }

    public function test_initiate_money_topup_creates_payment_and_returns_redirect_view(): void
    {
        $this->configureWfp();

        $student = User::factory()->student()->create();

        $response = $this->actingAs($student)
            ->post(route('wayforpay.money'), ['amount' => 500]);

        $response->assertOk()->assertViewIs('wayforpay.redirect');

        $this->assertDatabaseHas('payments', [
            'student_id'   => $student->id,
            'payment_type' => 'money',
            'amount'       => 500,
            'status'       => 'pending',
            'method'       => 'online',
        ]);
    }

    public function test_initiate_money_topup_validates_minimum_amount(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->post(route('wayforpay.money'), ['amount' => 5])
            ->assertSessionHasErrors('amount');
    }

    public function test_callback_returns_400_when_wfp_not_configured(): void
    {
        $this->postJson(route('wayforpay.callback'), [])
            ->assertStatus(400);
    }

    public function test_callback_returns_400_on_invalid_signature(): void
    {
        $this->configureWfp();

        $data = [
            'merchantAccount'   => 'test_merchant',
            'orderReference'    => 'ORDER-1',
            'amount'            => 100,
            'currency'          => 'UAH',
            'authCode'          => 'AUTH',
            'cardPan'           => '4111****1111',
            'transactionStatus' => 'Approved',
            'reasonCode'        => '1100',
            'merchantSignature' => 'bad_sig',
        ];

        $this->postJson(route('wayforpay.callback'), $data)
            ->assertStatus(400);
    }

    public function test_callback_marks_payment_paid_on_approved(): void
    {
        $this->configureWfp();

        $student = User::factory()->student()->create(['money_balance' => 0]);
        $orderRef = 'MONEY-99-9999';

        $payment = Payment::factory()->money()->create([
            'student_id' => $student->id,
            'amount'     => 400,
            'status'     => 'pending',
            'notes'      => "WayForPay: поповнення балансу | ref:{$orderRef}",
        ]);

        $data = [
            'merchantAccount'   => 'test_merchant',
            'orderReference'    => $orderRef,
            'amount'            => 400,
            'currency'          => 'UAH',
            'authCode'          => 'AUTH',
            'cardPan'           => '4111****1111',
            'transactionStatus' => 'Approved',
            'reasonCode'        => '1100',
        ];
        $signString = implode(';', array_values($data));
        $data['merchantSignature'] = hash_hmac('md5', $signString, 'test_secret');

        $this->postJson(route('wayforpay.callback'), $data)
            ->assertOk()
            ->assertJsonFragment(['status' => 'accept']);

        $this->assertEquals('paid', $payment->fresh()->status);
        $this->assertEquals(400, (float) $student->fresh()->money_balance);
    }

    public function test_callback_does_not_double_credit_already_paid_payment(): void
    {
        $this->configureWfp();

        $student  = User::factory()->student()->create(['money_balance' => 400]);
        $orderRef = 'MONEY-99-8888';

        $payment = Payment::factory()->money()->paid()->create([
            'student_id' => $student->id,
            'amount'     => 400,
            'notes'      => "ref:{$orderRef}",
        ]);

        $data = [
            'merchantAccount'   => 'test_merchant',
            'orderReference'    => $orderRef,
            'amount'            => 400,
            'currency'          => 'UAH',
            'authCode'          => 'AUTH',
            'cardPan'           => '4111****1111',
            'transactionStatus' => 'Approved',
            'reasonCode'        => '1100',
        ];
        $data['merchantSignature'] = hash_hmac('md5', implode(';', array_values($data)), 'test_secret');

        $this->postJson(route('wayforpay.callback'), $data)->assertOk();

        $this->assertEquals(400, (float) $student->fresh()->money_balance);
    }
}
