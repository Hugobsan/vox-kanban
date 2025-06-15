<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Apenas admins podem atualizar roles
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        
        return [
            'name' => [
                'sometimes', 
                'required', 
                'string', 
                'max:255', 
                Rule::unique('roles', 'name')->ignore($role->id)
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome do papel',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome do papel é obrigatório.',
            'name.string' => 'O nome do papel deve ser um texto.',
            'name.max' => 'O nome do papel não pode ter mais de 255 caracteres.',
            'name.unique' => 'Este nome de papel já existe.',
        ];
    }
}
