<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class StoreLabelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verifica se pode criar labels no board específico
        $board = Board::find($this->board_id);
        
        if (!$board) {
            return false;
        }
        
        return $this->user()->can('update', $board);
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
            'name' => ['required', 'string', 'max:255'],
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
            'board_id' => 'board',
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
            'board_id.required' => 'O board é obrigatório.',
            'board_id.integer' => 'O board deve ser um número.',
            'board_id.exists' => 'O board selecionado não existe.',
            'name.required' => 'O nome da etiqueta é obrigatório.',
            'name.string' => 'O nome da etiqueta deve ser um texto.',
            'name.max' => 'O nome da etiqueta não pode ter mais de :max caracteres.',
            'color.string' => 'A cor deve ser um texto.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (ex: #FFFFFF).',
            'icon.string' => 'O ícone deve ser um texto.',
            'icon.max' => 'O ícone não pode ter mais de :max caracteres.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Define cor padrão se não fornecida
        if (!$this->has('color')) {
            $this->merge(['color' => '#FFFFFF']);
        }
    }
}
