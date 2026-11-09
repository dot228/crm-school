<?php

namespace App\Repositories;

use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Enrollment::with(['student', 'group.course']);

        if (!empty($filters['group_id'])) {
            $query->where('group_id', $filters['group_id']);
        }
        if (!empty($filters['student_id'])) {
            $query->where('student_id', $filters['student_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Enrollment
    {
        return Enrollment::with(['student', 'group.course', 'payments'])->find($id);
    }

    public function create(array $data): Enrollment
    {
        return Enrollment::create($data);
    }

    public function update(Enrollment $enrollment, array $data): Enrollment
    {
        $enrollment->update($data);
        return $enrollment->fresh();
    }

    public function delete(Enrollment $enrollment): bool
    {
        return $enrollment->delete();
    }

    public function getByStudent(int $studentId): Collection
    {
        return Enrollment::with(['group.course'])->where('student_id', $studentId)->latest()->get();
    }

    public function getByGroup(int $groupId): Collection
    {
        return Enrollment::with('student')->where('group_id', $groupId)->latest()->get();
    }
}
