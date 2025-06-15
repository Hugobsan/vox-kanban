<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

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

            return $this->respond()->successResponse($task, 'Task created successfully!', 201);
        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Error creating task: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        //
    }
}
