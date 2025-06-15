<?php

namespace App\Http\Requests;

use App\Models\Board;
use Illuminate\Foundation\Http\FormRequest;

class StoreColumnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Pega o board a partir do binding do request
        $board = $this->route('board');
        
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
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'board_id' => 'board',
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
            'board_id.required' => 'O board é obrigatório.',
            'board_id.integer' => 'O board deve ser um número.',
            'board_id.exists' => 'O board selecionado não existe.',
            'name.required' => 'O nome da coluna é obrigatório.',
            'name.string' => 'O nome da coluna deve ser um texto.',
            'name.max' => 'O nome da coluna não pode ter mais de 255 caracteres.',
            'color.string' => 'A cor deve ser um texto.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (ex: #FFFFFF).',
            'order.integer' => 'A ordem deve ser um número.',
            'order.min' => 'A ordem deve ser maior ou igual a 0.',
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

        // Define board_id a partir do binding do request
        if (!$this->has('board_id') && $this->route('board')) {
            $this->merge(['board_id' => $this->route('board')->id]);
        }
        
        // Define ordem automaticamente se não fornecida
        if (!$this->has('order') && $this->has('board_id')) {
            $board = Board::find($this->board_id);
            if ($board) {
                $maxOrder = $board->columns()->max('order') ?? -1;
                $this->merge(['order' => $maxOrder + 1]);
            }
        }
    }
}
