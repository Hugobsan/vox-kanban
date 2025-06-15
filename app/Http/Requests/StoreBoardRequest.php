<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class StoreBoardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Board::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:10', 'regex:/^[A-Z]+$/', 'unique:boards,key'],
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
        $this->merge([
            'key' => strtoupper($this->key ?? ''),
        ]);
    }
}
