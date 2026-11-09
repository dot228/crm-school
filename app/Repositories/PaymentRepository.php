<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::with(['student', 'enrollment.group.course']);

        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Payment
    {
        return Payment::with(['student', 'enrollment.group.course'])->find($id);
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);
        return $payment->fresh();
    }

    public function delete(Payment $payment): bool
    {
        return $payment->delete();
    }

    public function getByStudent(int $studentId): Collection
    {
        return Payment::with(['enrollment.group.course'])
            ->where('student_id', $studentId)
            ->latest()
            ->get();
    }

    public function getTotalRevenue(): float
    {
        return (float) Payment::where('status', 'paid')->sum('amount');
    }
}
