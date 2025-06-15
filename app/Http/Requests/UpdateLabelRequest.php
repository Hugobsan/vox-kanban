<?php

namespace App\Http\Requests;

use App\Models\Label;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLabelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $label = $this->route('label');
        return $this->user()->can('update', $label->board);
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
            'icon' => ['sometimes', 'nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome da etiqueta',
            'color' => 'cor da etiqueta',
            'icon' => 'ícone da etiqueta',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'O nome da etiqueta é obrigatório.',
            'name.string' => 'O nome da etiqueta deve ser um texto.',
            'name.max' => 'O nome da etiqueta não pode ter mais de 255 caracteres.',
            'color.string' => 'A cor deve ser um texto.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (ex: #FFFFFF).',
            'icon.string' => 'O ícone deve ser um texto.',
            'icon.max' => 'O ícone não pode ter mais de 50 caracteres.',
        ];
    }
}
