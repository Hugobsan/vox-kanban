<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrar um novo usuário.
     */
    public function register(StoreUserRequest $request)
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Atribuir roles se fornecidas
            if ($request->has('roles') && !empty($request->roles)) {
                $roleNames = \App\Models\Role::whereIn('id', $request->roles)->pluck('name');
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
     * Autenticar usuário.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ], [
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'password.required' => 'A senha é obrigatória.',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Credenciais inválidas.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->respond()->successResponse([
            'user' => $user->load('roles'),
            'token' => $token,
        ], 'Login realizado com sucesso!');
    }

    /**
     * Logout do usuário.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->respond()->successResponse(null, 'Logout realizado com sucesso!');
    }

    /**
     * Obter usuário atual.
     */
    public function me(Request $request)
    {
        return $this->respond()->successResponse($request->user()->load('roles'), 'Dados do usuário atual.');
    }
}
