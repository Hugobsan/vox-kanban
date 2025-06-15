<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Models\Column;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Verifica se pode criar tasks na coluna específica
        $column = Column::find($this->column_id);
        
        if (!$column) {
            return false;
        }
        
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
            'column_id' => ['required', 'integer', 'exists:columns,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'assigned_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date', 'after:today'],
            'order' => ['sometimes', 'integer', 'min:0'],
            'labels' => ['sometimes', 'array'],
            'labels.*' => ['integer', 'exists:labels,id'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'column_id' => 'coluna',
            'title' => 'título',
            'description' => 'descrição',
            'assigned_user_id' => 'usuário responsável',
            'due_date' => 'data de vencimento',
            'order' => 'ordem',
            'labels' => 'etiquetas',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'column_id.required' => 'A coluna é obrigatória.',
            'column_id.integer' => 'A coluna deve ser um número.',
            'column_id.exists' => 'A coluna selecionada não existe.',
            'title.required' => 'O título é obrigatório.',
            'title.string' => 'O título deve ser um texto.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'description.string' => 'A descrição deve ser um texto.',
            'description.max' => 'A descrição não pode ter mais de 65535 caracteres.',
            'assigned_user_id.integer' => 'O usuário responsável deve ser um número.',
            'assigned_user_id.exists' => 'O usuário responsável selecionado não existe.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'due_date.after' => 'A data de vencimento deve ser posterior a hoje.',
            'order.integer' => 'A ordem deve ser um número.',
            'order.min' => 'A ordem deve ser maior ou igual a 0.',
            'labels.array' => 'As etiquetas devem ser uma lista.',
            'labels.*.integer' => 'Cada etiqueta deve ser um número.',
            'labels.*.exists' => 'Uma das etiquetas selecionadas não existe.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Verifica se o usuário atribuído tem acesso ao board
            if ($this->assigned_user_id && $this->column_id) {
                $column = Column::find($this->column_id);
                if ($column && !$column->board->hasUser($this->assigned_user_id)) {
                    $validator->errors()->add('assigned_user_id', 'O usuário responsável deve ter acesso ao board.');
                }
            }
            
            // Verifica se as labels pertencem ao mesmo board
            if ($this->labels && $this->column_id) {
                $column = Column::find($this->column_id);
                if ($column) {
                    $validLabels = $column->board->labels()->pluck('id')->toArray();
                    $invalidLabels = array_diff($this->labels, $validLabels);
                    
                    if (!empty($invalidLabels)) {
                        $validator->errors()->add('labels', 'Algumas etiquetas não pertencem a este board.');
                    }
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Define ordem automaticamente se não fornecida
        if (!$this->has('order') && $this->has('column_id')) {
            $column = Column::find($this->column_id);
            if ($column) {
                $this->merge(['order' => $column->getNextTaskOrder()]);
            }
        }
    }
}
