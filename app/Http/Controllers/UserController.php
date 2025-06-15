<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(15);

        return $this->respond()->view('users.index', ['users' => $users], $request);
    }

    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $this->assignRolesToUser($user, $request->roles);

            return $this->respond()->view('users.create', ['user' => $user->load('roles')], $request, 201);

        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao criar usuário: ' . $e->getMessage(), 500);
        }
    }

    public function show(User $user, Request $request)
    {
        $this->authorize('view', $user);

        return $this->respond()->view('users.show', ['user' => $user->load('roles')], $request);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            if ($request->has('roles')) {
                $this->updateUserRoles($user, $request->roles);
            }

            return $this->respond()->view('users.edit', ['user' => $user->fresh()->load('roles')], $request);

        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao atualizar usuário: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(User $user, Request $request)
    {
        $this->authorize('delete', $user);

        try {
            $user->delete();
            return $this->respond()->view('users.index', ['message' => 'Usuário removido com sucesso!'], $request);
        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao remover usuário: ' . $e->getMessage(), 500);
        }
    }

    private function assignRolesToUser(User $user, $roles): void
    {
        if (!empty($roles)) {
            $roleNames = is_array($roles) ? $roles : [$roles];
            foreach ($roleNames as $roleName) {
                $user->assignRole($roleName);
            }
        } else {
            $user->assignRole('user');
        }
    }

    private function updateUserRoles(User $user, $roles): void
    {
        foreach ($user->roles as $role) {
            $user->removeRole($role->name);
        }

        if (!empty($roles)) {
            $roleNames = is_array($roles) ? $roles : [$roles];
            foreach ($roleNames as $roleName) {
                $user->assignRole($roleName);
            }
        } else {
            $user->assignRole('user');
        }
    }
}
