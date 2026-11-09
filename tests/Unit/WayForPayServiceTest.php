<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Services\WayForPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WayForPayServiceTest extends TestCase
{
    use RefreshDatabase;

    private WayForPayService $service;

    private function configureSetting(): void
    {
        Setting::set('wfp_merchant_account', 'test_merchant');
        Setting::set('wfp_merchant_secret',  'test_secret');
        Setting::set('wfp_merchant_domain',  'example.com');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WayForPayService();
    }

    public function test_is_configured_returns_false_when_settings_missing(): void
    {
        $this->assertFalse($this->service->isConfigured());
    }

    public function test_is_configured_returns_true_when_settings_present(): void
    {
        $this->configureSetting();
        $this->assertTrue($this->service->isConfigured());
    }

    public function test_build_form_returns_required_keys(): void
    {
        $this->configureSetting();

        $form = $this->service->buildForm([
            'order_reference' => 'ORDER-1-123',
            'order_date'      => 1700000000,
            'amount'          => 500,
            'product_name'    => 'Test',
            'product_count'   => 1,
            'product_price'   => 500,
        ]);

        $this->assertArrayHasKey('pay_url', $form);
        $this->assertArrayHasKey('merchantSignature', $form);
        $this->assertArrayHasKey('orderReference', $form);
        $this->assertEquals('ORDER-1-123', $form['orderReference']);
        $this->assertEquals(500, $form['amount']);
        $this->assertEquals('UAH', $form['currency']);
    }

    public function test_build_form_signature_is_valid_hmac(): void
    {
        $this->configureSetting();

        $form = $this->service->buildForm([
            'order_reference' => 'REF-42',
            'order_date'      => 1700000000,
            'amount'          => 300,
            'product_name'    => 'Lesson',
            'product_count'   => 1,
            'product_price'   => 300,
        ]);

        $signString = implode(';', [
            'test_merchant', 'example.com', 'REF-42', 1700000000, 300, 'UAH', 'Lesson', 1, 300,
        ]);
        $expected = hash_hmac('md5', $signString, 'test_secret');

        $this->assertEquals($expected, $form['merchantSignature']);
    }

    public function test_verify_callback_returns_false_when_required_keys_missing(): void
    {
        $this->configureSetting();
        $this->assertFalse($this->service->verifyCallback(['merchantAccount' => 'x']));
    }

    public function test_verify_callback_returns_false_on_wrong_signature(): void
    {
        $this->configureSetting();

        $data = [
            'merchantAccount'   => 'test_merchant',
            'orderReference'    => 'REF-1',
            'amount'            => 100,
            'currency'          => 'UAH',
            'authCode'          => 'AUTH',
            'cardPan'           => '4111****1111',
            'transactionStatus' => 'Approved',
            'reasonCode'        => '1100',
            'merchantSignature' => 'wrong_signature',
        ];

        $this->assertFalse($this->service->verifyCallback($data));
    }

    public function test_verify_callback_returns_true_on_correct_signature(): void
    {
        $this->configureSetting();

        $data = [
            'merchantAccount'   => 'test_merchant',
            'orderReference'    => 'REF-1',
            'amount'            => 100,
            'currency'          => 'UAH',
            'authCode'          => 'AUTH',
            'cardPan'           => '4111****1111',
            'transactionStatus' => 'Approved',
            'reasonCode'        => '1100',
        ];

        $signString = implode(';', array_values($data));
        $data['merchantSignature'] = hash_hmac('md5', $signString, 'test_secret');

        $this->assertTrue($this->service->verifyCallback($data));
    }

    public function test_build_callback_response_contains_valid_signature(): void
    {
        $this->configureSetting();

        $resp = $this->service->buildCallbackResponse('REF-10', 'accept', '1700000000');

        $expected = hash_hmac('md5', 'REF-10;accept;1700000000', 'test_secret');

        $this->assertEquals('REF-10', $resp['orderReference']);
        $this->assertEquals('accept', $resp['status']);
        $this->assertEquals($expected, $resp['signature']);
    }
}
