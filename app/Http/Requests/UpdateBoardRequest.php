<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBoardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('board'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $board = $this->route('board');
        
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'key' => [
                'sometimes', 
                'required', 
                'string', 
                'max:10', 
                'regex:/^[A-Z]+$/', 
                Rule::unique('boards', 'key')->ignore($board->id)
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Nome do board',
            'key' => 'Chave do board',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute é obrigatório.',
            'string' => ':attribute deve ser um texto.',
            'max' => ':attribute não pode ter mais de :max caracteres.',
            'key.regex' => ':attribute deve conter apenas letras maiúsculas.',
            'key.unique' => ':attribute já está sendo utilizada.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        
        if ($this->has('key')) {
            $data['key'] = strtoupper($this->key);
        }
        
        $this->merge($data);
    }
}