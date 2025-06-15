<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InviteBoardUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verifica se pode gerenciar usuários no board específico
        $board = Board::find($this->board_id);
        
        if (!$board) {
            return false;
        }
        
        return $this->user()->can('manageUsers', $board);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'board_id' => ['required', 'integer', 'exists:boards,id'],
            'email' => [
                'required', 
                'email',
                'max:255'
            ],
            'role_in_board' => ['required', 'string', Rule::in(['owner', 'editor', 'viewer'])],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'board_id' => 'board',
            'email' => 'e-mail',
            'role_in_board' => 'papel no board',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'required' => 'O :attribute é obrigatório.',
            'email' => 'O :attribute deve ser um e-mail válido.',
            'integer' => 'O :attribute deve ser um número.',
            'exists' => 'O :attribute selecionado não existe.',
            'string' => 'O :attribute deve ser um texto.',
            'in' => 'O :attribute deve ser: owner, editor ou viewer.',
            'max' => 'O :attribute deve ter no máximo :max caracteres.',
        ];
    }
}
