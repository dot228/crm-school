<?php

namespace App\Services;

use App\Models\Group;
use App\Repositories\Interfaces\GroupRepositoryInterface;

class GroupService
{
    public function __construct(protected GroupRepositoryInterface $groupRepo) {}

    public function getList(array $filters = [], int $perPage = 15)
    {
        return $this->groupRepo->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Group
    {
        return $this->groupRepo->findById($id);
    }

    public function create(array $data): Group
    {
        return $this->groupRepo->create($data);
    }

    public function update(Group $group, array $data): Group
    {
        return $this->groupRepo->update($group, $data);
    }

    public function delete(Group $group): bool
    {
        return $this->groupRepo->delete($group);
    }

    public function getByCourse(int $courseId)
    {
        return $this->groupRepo->getByCourse($courseId);
    }

    public function getByTeacher(int $teacherId)
    {
        return $this->groupRepo->getByTeacher($teacherId);
    }
}
