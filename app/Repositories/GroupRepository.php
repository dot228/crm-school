<?php

namespace App\Repositories;

use App\Models\Group;
use App\Repositories\Interfaces\GroupRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class GroupRepository implements GroupRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Group::with(['course', 'teacher']);

        if (!empty($filters['course_id'])) {
            $query->where('course_id', $filters['course_id']);
        }
        if (!empty($filters['teacher_id'])) {
            $query->where('teacher_id', $filters['teacher_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Group
    {
        return Group::with(['course', 'teacher', 'enrollments.student'])->find($id);
    }

    public function create(array $data): Group
    {
        return Group::create($data);
    }

    public function update(Group $group, array $data): Group
    {
        $group->update($data);
        return $group->fresh();
    }

    public function delete(Group $group): bool
    {
        return $group->delete();
    }

    public function getByCourse(int $courseId): Collection
    {
        return Group::where('course_id', $courseId)->where('status', 'active')->get();
    }

    public function getByTeacher(int $teacherId): Collection
    {
        return Group::where('teacher_id', $teacherId)->with('course')->latest()->get();
    }
}
