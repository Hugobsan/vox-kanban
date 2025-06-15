<?php

namespace App\Http\Requests;

use App\Models\RoleUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Apenas admins podem atribuir roles
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required', 
                'integer', 
                'exists:users,id',
                Rule::unique('role_user')->where(function ($query) {
                    return $query->where('role_id', $this->role_id)
                                ->whereNull('revoked_at');
                })
            ],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'assigned_at' => ['sometimes', 'date'],
            'revoked_at' => ['sometimes', 'nullable', 'date', 'after:assigned_at'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'usuário',
            'role_id' => 'papel',
            'assigned_at' => 'data de atribuição',
            'revoked_at' => 'data de revogação',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'O usuário é obrigatório.',
            'user_id.integer' => 'O usuário deve ser um número.',
            'user_id.exists' => 'O usuário selecionado não existe.',
            'user_id.unique' => 'Este usuário já possui este papel ativo.',
            'role_id.required' => 'O papel é obrigatório.',
            'role_id.integer' => 'O papel deve ser um número.',
            'role_id.exists' => 'O papel selecionado não existe.',
            'assigned_at.date' => 'A data de atribuição deve ser uma data válida.',
            'revoked_at.date' => 'A data de revogação deve ser uma data válida.',
            'revoked_at.after' => 'A data de revogação deve ser posterior à data de atribuição.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Define data de atribuição como agora se não fornecida
        if (!$this->has('assigned_at')) {
            $this->merge(['assigned_at' => now()]);
        }
    }
}
