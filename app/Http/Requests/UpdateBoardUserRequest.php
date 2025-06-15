<?php

namespace App\Http\Requests;

use App\Models\BoardUser;
use App\Enums\RoleInBoard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBoardUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $boardUser = $this->route('boardUser');
        return $this->user()->can('manageUsers', $boardUser->board);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role_in_board' => ['sometimes', 'required', 'string', Rule::in(['owner', 'editor', 'viewer'])],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
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
            'string' => 'O :attribute deve ser um texto.',
            'in' => 'O :attribute deve ser: owner, editor ou viewer.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $boardUser = $this->route('boardUser');
            
            // Impede que o último owner seja rebaixado
            if ($this->role_in_board && $this->role_in_board !== 'owner') {
                $ownerCount = BoardUser::where('board_id', $boardUser->board_id)
                    ->where('role_in_board', 'owner')
                    ->count();
                
                if ($boardUser->role_in_board === RoleInBoard::Owner && $ownerCount <= 1) {
                    $validator->errors()->add('role_in_board', 'Deve haver pelo menos um owner no board.');
                }
            }
        });
    }
}
