<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserService $userService) {}

    private function allowedRoleIds(): array
    {
        return auth()->user()->isAdmin() ? [1, 2, 3, 4] : [3, 4];
    }

    private function allowedRoles(): array
    {
        return array_intersect_key(User::ROLES, array_flip($this->allowedRoleIds()));
    }

    private function assertCanManage(User $user): void
    {
        if (!auth()->user()->isAdmin() && $user->role_id <= 2) {
            abort(403, 'Недостатньо прав для управління цим користувачем.');
        }
    }

    public function index(Request $request)
    {
        $filters = $request->only(['role_id', 'status', 'search']);
        $users   = $this->userService->getList($filters);
        return view('users.index', compact('users', 'filters'));
    }

    public function create()
    {
        $roles = $this->allowedRoles();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $allowedIds = implode(',', $this->allowedRoleIds());
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'unique:users,email'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
            'role_id'          => ['required', "in:{$allowedIds}"],
            'phone'            => ['nullable', 'string', 'max:20'],
            'status'           => ['required', 'in:active,inactive,blocked'],
            'bio'              => ['nullable', 'string'],
            'avatar_file'      => ['nullable', 'image', 'max:2048'],
            'date_of_birth'    => ['nullable', 'date', 'before:today'],
            'guardian_name'    => ['nullable', 'string', 'max:255'],
            'guardian_phone'   => ['nullable', 'string', 'max:20'],
            'guardian_email'   => ['nullable', 'email', 'max:255'],
            'guardian_relation'=> ['nullable', 'string', 'max:100'],
        ]);

        if ($request->integer('role_id') === \App\Models\User::ROLE_STUDENT) {
            $age = $request->filled('date_of_birth')
                ? \Carbon\Carbon::parse($request->date_of_birth)->age
                : null;
            if ($age !== null && $age < 18 && empty($data['guardian_name'])) {
                return back()->withErrors(['guardian_name' => 'Для учнів до 18 років необхідно вказати відповідальну особу.'])->withInput();
            }
        }

        $this->userService->create($data);
        return redirect()->route('users.index')->with('success', 'Користувача створено успішно.');
    }

    public function show(int $id)
    {
        $user = $this->userService->findById($id);
        abort_if(!$user, 404);
        $this->assertCanManage($user);
        return view('users.show', compact('user'));
    }

    public function edit(int $id)
    {
        $user  = $this->userService->findById($id);
        abort_if(!$user, 404);
        $this->assertCanManage($user);
        $roles = $this->allowedRoles();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, int $id)
    {
        $user = $this->userService->findById($id);
        abort_if(!$user, 404);
        $this->assertCanManage($user);

        $allowedIds = implode(',', $this->allowedRoleIds());
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'email'            => ['required', 'email', 'unique:users,email,' . $id],
            'password'         => ['nullable', 'string', 'min:6', 'confirmed'],
            'role_id'          => ['required', "in:{$allowedIds}"],
            'phone'            => ['nullable', 'string', 'max:20'],
            'status'           => ['required', 'in:active,inactive,blocked'],
            'bio'              => ['nullable', 'string'],
            'avatar_file'      => ['nullable', 'image', 'max:2048'],
            'date_of_birth'    => ['nullable', 'date', 'before:today'],
            'guardian_name'    => ['nullable', 'string', 'max:255'],
            'guardian_phone'   => ['nullable', 'string', 'max:20'],
            'guardian_email'   => ['nullable', 'email', 'max:255'],
            'guardian_relation'=> ['nullable', 'string', 'max:100'],
        ]);

        if ($request->integer('role_id') === \App\Models\User::ROLE_STUDENT) {
            $age = $request->filled('date_of_birth')
                ? \Carbon\Carbon::parse($request->date_of_birth)->age
                : null;
            if ($age !== null && $age < 18 && empty($data['guardian_name'])) {
                return back()->withErrors(['guardian_name' => 'Для учнів до 18 років необхідно вказати відповідальну особу.'])->withInput();
            }
        }

        $this->userService->update($user, $data);
        return redirect()->route('users.index')->with('success', 'Користувача оновлено успішно.');
    }

    public function destroy(int $id)
    {
        $user = $this->userService->findById($id);
        abort_if(!$user, 404);
        $this->assertCanManage($user);

        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'Ви не можете видалити власний акаунт.');
        }

        $this->userService->delete($user);
        return redirect()->route('users.index')->with('success', 'Користувача видалено.');
    }

    public function block(int $id)
    {
        $user = $this->userService->findById($id);
        abort_if(!$user, 404);
        $this->assertCanManage($user);

        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Ви не можете заблокувати власний акаунт.');
        }

        $newStatus = $user->status === 'blocked' ? 'active' : 'blocked';
        $user->update(['status' => $newStatus]);

        $label = $newStatus === 'blocked' ? 'заблоковано' : 'розблоковано';
        return redirect()->back()->with('success', "Користувача {$label}.");
    }
}
