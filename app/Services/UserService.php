<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function __construct(protected UserRepositoryInterface $userRepo) {}

    public function getList(array $filters = [], int $perPage = 15)
    {
        return $this->userRepo->paginate($filters, $perPage);
    }

    public function findById(int $id): ?User
    {
        return $this->userRepo->findById($id);
    }

    public function create(array $data): User
    {
        $data['password'] = Hash::make($data['password']);

        if (isset($data['avatar_file'])) {
            $data['avatar'] = $data['avatar_file']->store('avatars', 'public');
            unset($data['avatar_file']);
        }

        return $this->userRepo->create($data);
    }

    public function update(User $user, array $data): User
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if (isset($data['avatar_file'])) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $data['avatar_file']->store('avatars', 'public');
            unset($data['avatar_file']);
        }

        return $this->userRepo->update($user, $data);
    }

    public function delete(User $user): bool
    {
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        return $this->userRepo->delete($user);
    }

    public function getTeachers()
    {
        return $this->userRepo->getByRole(User::ROLE_TEACHER);
    }

    public function getStudents()
    {
        return $this->userRepo->getByRole(User::ROLE_STUDENT);
    }

    public function getManagers()
    {
        return $this->userRepo->getByRole(User::ROLE_MANAGER);
    }
}
