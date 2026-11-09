<?php

namespace App\Repositories\Interfaces;

use App\Models\Payment;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Payment;
    public function create(array $data): Payment;
    public function update(Payment $payment, array $data): Payment;
    public function delete(Payment $payment): bool;
    public function getByStudent(int $studentId): \Illuminate\Database\Eloquent\Collection;
    public function getTotalRevenue(): float;
}
