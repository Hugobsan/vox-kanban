<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('roles')
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->paginate(15);

        return $this->respond()->successResponse($users, 'Lista de usuários.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Atribuir roles se especificadas
            if ($request->has('roles') && !empty($request->roles)) {
                $roleNames = is_array($request->roles) ? $request->roles : [$request->roles];
                foreach ($roleNames as $roleName) {
                    $user->assignRole($roleName);
                }
            } else {
                // Se não há roles especificadas, atribuir role 'user' por padrão
                $user->assignRole('user');
            }

            return $this->respond()->successResponse($user->load('roles'), 'Usuário criado com sucesso!', 201);

        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao criar usuário: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        return $this->respond()->successResponse($user->load('roles'), 'Dados do usuário.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            // Atualizar senha apenas se fornecida
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // Atualizar roles se fornecidas
            if ($request->has('roles')) {
                // Remover todas as roles atuais
                foreach ($user->roles as $role) {
                    $user->removeRole($role->name);
                }

                // Atribuir novas roles
                if (!empty($request->roles)) {
                    $roleNames = is_array($request->roles) ? $request->roles : [$request->roles];
                    foreach ($roleNames as $roleName) {
                        $user->assignRole($roleName);
                    }
                } else {
                    // Se não há roles especificadas, atribuir role 'user' por padrão
                    $user->assignRole('user');
                }
            }

            return $this->respond()->successResponse($user->fresh()->load('roles'), 'Usuário atualizado com sucesso!');

        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao atualizar usuário: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        try {
            $user->delete();
            return $this->respond()->successResponse(null, 'Usuário removido com sucesso!');
        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao remover usuário: ' . $e->getMessage(), 500);
        }
    }
}
