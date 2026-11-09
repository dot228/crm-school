<?php

namespace App\Services;

use App\Models\Course;
use App\Repositories\Interfaces\CourseRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class CourseService
{
    public function __construct(protected CourseRepositoryInterface $courseRepo) {}

    public function getList(array $filters = [], int $perPage = 15)
    {
        return $this->courseRepo->paginate($filters, $perPage);
    }

    public function findById(int $id): ?Course
    {
        return $this->courseRepo->findById($id);
    }

    public function create(array $data): Course
    {
        if (isset($data['image_file'])) {
            $data['image'] = $data['image_file']->store('courses', 'public');
            unset($data['image_file']);
        }
        return $this->courseRepo->create($data);
    }

    public function update(Course $course, array $data): Course
    {
        if (isset($data['image_file'])) {
            if ($course->image) {
                Storage::disk('public')->delete($course->image);
            }
            $data['image'] = $data['image_file']->store('courses', 'public');
            unset($data['image_file']);
        }
        return $this->courseRepo->update($course, $data);
    }

    public function delete(Course $course): bool
    {
        if ($course->image) {
            Storage::disk('public')->delete($course->image);
        }
        return $this->courseRepo->delete($course);
    }

    public function getActiveCourses()
    {
        return $this->courseRepo->getActive();
    }
}
