<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreColumnRequest;
use App\Http\Requests\UpdateColumnRequest;
use App\Models\Board;
use App\Models\Column;
use Illuminate\Support\Facades\DB;

class ColumnController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Board $board)
    {
        $this->authorize('view', $board);

        $columns = Column::with('board')
            ->where('board_id', $board->id)
            ->when(request()->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(15);

        return $this->respond()->view('columns.index', ['columns' => $columns], request());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreColumnRequest $request, Board $board)
    {
        
        try {
            $columnData = $request->validated();
            $columnData['board_id'] = $board->id;
            
            $column = Column::create($columnData);
            return $this->respond()->successResponse($column, 'Coluna criada com sucesso!', 201);
        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao criar coluna: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Board $board, Column $column)
    {
        $this->authorize('view', $board);

        return $this->respond()->view('columns.show', ['column' => $column->load('tasks')], request());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateColumnRequest $request, Board $board, Column $column)
    {
        $this->authorize('update', $board);
        DB::beginTransaction();
        try {
            // Verifica se a ordem foi alterada e incrementa as ordens subsequentes
            if ($request->has('order') && $request->order !== $column->order) {
                Column::where('board_id', $board->id)
                    ->where('order', '>=', $request->order)
                    ->increment('order');
            }
            $column->update($request->validated());
            
            DB::commit();
            return $this->respond()->successResponse($column, 'Coluna atualizada com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respond()->errorResponse('Erro ao atualizar coluna: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Board $board, Column $column)
    {
        $this->authorize('update', $board);

        try {
            $column->delete();
            return $this->respond()->successResponse(null, 'Coluna removida com sucesso!');
        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao remover coluna: ' . $e->getMessage(), 500);
        }
    }
}
