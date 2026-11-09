<?php

namespace App\Repositories\Interfaces;

use App\Models\Group;
use Illuminate\Pagination\LengthAwarePaginator;

interface GroupRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findById(int $id): ?Group;
    public function create(array $data): Group;
    public function update(Group $group, array $data): Group;
    public function delete(Group $group): bool;
    public function getByCourse(int $courseId): \Illuminate\Database\Eloquent\Collection;
    public function getByTeacher(int $teacherId): \Illuminate\Database\Eloquent\Collection;
}
