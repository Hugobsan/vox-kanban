<?php

namespace App\Http\Requests;

use App\Models\Task;
use App\Models\Column;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $task = $this->route('task');
        return $this->user()->can('update', $task->board);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'column_id' => ['sometimes', 'integer', 'exists:columns,id'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'assigned_user_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'due_date' => ['sometimes', 'nullable', 'date', 'after:today'],
            'completed_at' => ['sometimes', 'nullable', 'date'],
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
            'completed_at' => 'data de conclusão',
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
            'completed_at.date' => 'A data de conclusão deve ser uma data válida.',
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
            $task = $this->route('task');
            
            // Verifica se a nova coluna pertence ao mesmo board
            if ($this->column_id && $this->column_id != $task->column_id) {
                $newColumn = Column::find($this->column_id);
                if ($newColumn && $newColumn->board_id !== $task->column->board_id) {
                    $validator->errors()->add('column_id', 'A nova coluna deve pertencer ao mesmo board.');
                }
            }
            
            // Verifica se o usuário atribuído tem acesso ao board
            if ($this->assigned_user_id) {
                if (!$task->board->hasUser($this->assigned_user_id)) {
                    $validator->errors()->add('assigned_user_id', 'O usuário responsável deve ter acesso ao board.');
                }
            }
            
            // Verifica se as labels pertencem ao mesmo board
            if ($this->labels) {
                $validLabels = $task->board->labels()->pluck('id')->toArray();
                $invalidLabels = array_diff($this->labels, $validLabels);
                
                if (!empty($invalidLabels)) {
                    $validator->errors()->add('labels', 'Algumas etiquetas não pertencem a este board.');
                }
            }
        });
    }
}
