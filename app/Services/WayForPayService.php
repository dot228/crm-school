<?php

namespace App\Services;

use App\Models\Setting;

class WayForPayService
{
    const PAY_URL = 'https://secure.wayforpay.com/pay';

    public function isConfigured(): bool
    {
        return !empty(Setting::get('wfp_merchant_account'))
            && !empty(Setting::get('wfp_merchant_secret'));
    }

    public function getMerchantAccount(): string
    {
        return Setting::get('wfp_merchant_account', '');
    }

    private function getSecret(): string
    {
        return Setting::get('wfp_merchant_secret', '');
    }

    private function getDomain(): string
    {
        return Setting::get('wfp_merchant_domain', parse_url(config('app.url'), PHP_URL_HOST));
    }

    public function buildForm(array $params): array
    {
        $merchantAccount = $this->getMerchantAccount();
        $merchantDomain  = $this->getDomain();
        $orderReference  = $params['order_reference'];
        $orderDate       = $params['order_date'];
        $amount          = $params['amount'];
        $currency        = $params['currency'] ?? 'UAH';
        $productName     = $params['product_name'];
        $productCount    = $params['product_count'] ?? 1;
        $productPrice    = $params['product_price'] ?? $amount;

        $signString = implode(';', [
            $merchantAccount,
            $merchantDomain,
            $orderReference,
            $orderDate,
            $amount,
            $currency,
            $productName,
            $productCount,
            $productPrice,
        ]);

        $signature = hash_hmac('md5', $signString, $this->getSecret());

        return [
            'pay_url'           => self::PAY_URL,
            'merchantAccount'   => $merchantAccount,
            'merchantDomainName'=> $merchantDomain,
            'orderReference'    => $orderReference,
            'orderDate'         => $orderDate,
            'amount'            => $amount,
            'currency'          => $currency,
            'productName'       => $productName,
            'productCount'      => $productCount,
            'productPrice'      => $productPrice,
            'merchantSignature' => $signature,
            'returnUrl'         => route('student.payments'),
            'serviceUrl'        => route('wayforpay.callback'),
            'language'          => 'UA',
        ];
    }

    public function verifyCallback(array $data): bool
    {
        $required = [
            'merchantAccount', 'orderReference', 'amount',
            'currency', 'authCode', 'cardPan', 'transactionStatus', 'reasonCode',
        ];

        foreach ($required as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }

        $signString = implode(';', [
            $data['merchantAccount'],
            $data['orderReference'],
            $data['amount'],
            $data['currency'],
            $data['authCode'],
            $data['cardPan'],
            $data['transactionStatus'],
            $data['reasonCode'],
        ]);

        $expected = hash_hmac('md5', $signString, $this->getSecret());

        return hash_equals($expected, $data['merchantSignature'] ?? '');
    }

    public function buildCallbackResponse(string $orderReference, string $status, string $time): array
    {
        $signString = implode(';', [$orderReference, $status, $time]);
        return [
            'orderReference' => $orderReference,
            'status'         => $status,
            'time'           => $time,
            'signature'      => hash_hmac('md5', $signString, $this->getSecret()),
        ];
    }
}
