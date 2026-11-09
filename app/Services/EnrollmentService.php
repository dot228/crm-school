<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Repositories\Interfaces\EnrollmentRepositoryInterface;

class EnrollmentService
{
    public function __construct(protected EnrollmentRepositoryInterface $enrollmentRepo) {}

    public function getList(array $filters = [], int $perPage = 15)
    {
        return $this->enrollmentRepo->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Enrollment
    {
        return $this->enrollmentRepo->findById($id);
    }

    public function create(array $data): Enrollment
    {
        $data['enrolled_at'] = $data['enrolled_at'] ?? now()->toDateString();
        return $this->enrollmentRepo->create($data);
    }

    public function update(Enrollment $enrollment, array $data): Enrollment
    {
        return $this->enrollmentRepo->update($enrollment, $data);
    }

    public function delete(Enrollment $enrollment): bool
    {
        return $this->enrollmentRepo->delete($enrollment);
    }

    public function getByStudent(int $studentId)
    {
        return $this->enrollmentRepo->getByStudent($studentId);
    }

    public function getByGroup(int $groupId)
    {
        return $this->enrollmentRepo->getByGroup($groupId);
    }
}
