<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        return $this->user() && $this->user()->can('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id ?? $this->route('user');
        
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $userId],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'roles' => ['nullable', 'array', function ($attribute, $value, $fail) {
                $this->validateRoles($value, $fail);
            }],
            'roles.*' => ['exists:roles,id'],
        ];
    }

    /**
     * Valida se o usuário pode atribuir as roles especificadas.
     */
    protected function validateRoles($roles, $fail): void
    {
        if (empty($roles)) {
            return; // Sem roles é sempre permitido
        }

        $user = $this->user();
        
        // Se há usuário autenticado, verificar permissões
        if (!$user->can('assignRoles', \App\Models\User::class)) {
            $roleNames = \App\Models\Role::whereIn('id', $roles)->pluck('name');
            foreach ($roleNames as $roleName) {
                if (!$user->can('canAssignRole', [\App\Models\User::class, $roleName])) {
                    $fail("Você não tem permissão para atribuir a role: {$roleName}");
                    break;
                }
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.string' => 'O nome deve ser um texto.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'email.required' => 'O email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.unique' => 'Este email já está sendo usado.',
            'password.confirmed' => 'A confirmação da senha não confere.',
            'roles.array' => 'Os papéis devem ser uma lista.',
            'roles.*.exists' => 'Um ou mais papéis selecionados não existem.',
        ];
    }
}
