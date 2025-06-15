<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateColumnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $column = $this->route('column');
        return $this->user()->can('update', $column->board);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'color' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome da coluna',
            'color' => 'cor da coluna',
            'order' => 'ordem da coluna',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da coluna é obrigatório.',
            'name.string' => 'O nome da coluna deve ser um texto.',
            'name.max' => 'O nome da coluna não pode ter mais de 255 caracteres.',
            'color.string' => 'A cor deve ser um texto.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (ex: #FFFFFF).',
            'order.integer' => 'A ordem deve ser um número.',
            'order.min' => 'A ordem deve ser maior ou igual a 0.',
        ];
    }
}
