<?php

namespace App\Services;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;

class PaymentService
{
    public function __construct(protected PaymentRepositoryInterface $paymentRepo) {}

    public function getList(array $filters = [], int $perPage = 15)
    {
        return $this->paymentRepo->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Payment
    {
        return $this->paymentRepo->findById($id);
    }

    public function create(array $data): Payment
    {
        $data['payment_date'] = $data['payment_date'] ?? now()->toDateString();
        return $this->paymentRepo->create($data);
    }

    public function update(Payment $payment, array $data): Payment
    {
        return $this->paymentRepo->update($payment, $data);
    }

    public function delete(Payment $payment): bool
    {
        return $this->paymentRepo->delete($payment);
    }

    public function getByStudent(int $studentId)
    {
        return $this->paymentRepo->getByStudent($studentId);
    }

    public function getTotalRevenue(): float
    {
        return $this->paymentRepo->getTotalRevenue();
    }
}
