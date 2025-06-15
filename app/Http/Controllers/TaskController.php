<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TaskController extends Controller
{


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTaskRequest $request)
    {
        $column = Column::findOrFail($request->column_id);
        $this->authorize('update', $column->board);
        DB::beginTransaction();
        try {
            // Armazena a task
            $task = Task::create($request->validated());

            // Se houver labels, associa à task
            if ($request->has('labels') && !empty($request->labels)) {
                $task->labels()->sync($request->labels);
            }

            // Recarrega a task com as labels associadas
            $task->load('labels');

            DB::commit();
            return $this->respond()->successResponse($task, 'Task created successfully!', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respond()->errorResponse('Error creating task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task)
    {
        $this->authorize('view', $task->board);

        // Carrega as labels associadas à task
        $task->load(['labels', 'assignedUser', 'column']);

        return $this->respond()->view('task.show', [
            'task' => $task,
        ], $request);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $this->authorize('update', $task->board);

        DB::beginTransaction();
        try {
            // Se houver mudança de order, atualizar as demais tasks com valor maior que a nova order
            if ($request->has('order') && $request->order !== $task->order) {
                Task::where('column_id', $request->column_id ?? $task->column_id)
                    ->where('order', '>=', $request->order)
                    ->where('id', '!=', $task->id)
                    ->increment('order');
            }

            // Atualiza a task com os dados validados
            $task->update($request->validated());

            // Se houver labels, e não for labels já associadas, associa à task
            if ($request->has('labels') && !empty($request->labels)) {
                $task->labels()->sync($request->labels);
            }

            // Recarrega a task com as labels associadas
            $task->load('labels');

            DB::commit();
            return $this->respond()->successResponse($task, 'Task updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respond()->errorResponse('Error updating task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $this->authorize('update', $task->board);

        $task->delete();

        return $this->respond()->successResponse(null, 'Task deleted successfully!');
    }
}
