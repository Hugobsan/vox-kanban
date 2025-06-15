<?php

namespace App\Http\Requests;

use App\Models\RoleUser;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Apenas admins podem atualizar atribuições de roles
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
            'assigned_at.date' => 'A data de atribuição deve ser uma data válida.',
            'revoked_at.date' => 'A data de revogação deve ser uma data válida.',
            'revoked_at.after' => 'A data de revogação deve ser posterior à data de atribuição.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $roleUser = $this->route('roleUser');
            
            // Se está definindo revoked_at, deve ser posterior ao assigned_at atual
            if ($this->revoked_at && $roleUser->assigned_at) {
                if ($this->revoked_at <= $roleUser->assigned_at) {
                    $validator->errors()->add('revoked_at', 'A data de revogação deve ser posterior à data de atribuição atual.');
                }
            }
        });
    }
}
