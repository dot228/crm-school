<?php

namespace App\Repositories\Interfaces;

use App\Models\Enrollment;
use Illuminate\Pagination\LengthAwarePaginator;

interface EnrollmentRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Enrollment;
    public function create(array $data): Enrollment;
    public function update(Enrollment $enrollment, array $data): Enrollment;
    public function delete(Enrollment $enrollment): bool;
    public function getByStudent(int $studentId): \Illuminate\Database\Eloquent\Collection;
    public function getByGroup(int $groupId): \Illuminate\Database\Eloquent\Collection;
}
