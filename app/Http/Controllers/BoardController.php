<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBoardRequest;
use App\Http\Requests\UpdateBoardRequest;
use App\Models\Board;
use App\Models\Column;
use App\Enums\RoleInBoard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BoardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Board::class);

        $user = $request->user();

        $boards = $user->boards()
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->respond()->view('kanban', ['boards' => $boards], $request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBoardRequest $request)
    {
        $this->authorize('create', Board::class);
        DB::beginTransaction();
        try {
            $user = $request->user();
            
            // Create the board
            $board = Board::create([
                'name' => $request->name,
                'key' => $request->key,
            ]);

            // Add user as owner
            $board->boardUsers()->create([
                'user_id' => $user->id,
                'role_in_board' => RoleInBoard::Owner,
            ]);

            // Create default columns
            $defaultColumns = [
                ['name' => 'A Fazer', 'order' => 1],
                ['name' => 'Em Progresso', 'order' => 2],
                ['name' => 'Concluído', 'order' => 3],
            ];

            foreach ($defaultColumns as $columnData) {
                Column::create([
                    'name' => $columnData['name'],
                    'order' => $columnData['order'],
                    'board_id' => $board->id,
                ]);
            }

            // Load the board with relationships
            $board->load(['columns', 'boardUsers']);
            $board->loadCount(['columns', 'tasks']);

            DB::commit();
            return $this->respond()->view('boards.store', ['board' => $board], $request, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respond()->errorResponse('Erro ao criar quadro: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Board $board)
    {
        $user = $request->user();
        
        // Check if user has access to this board
        if (!$board->hasUser($user->id)) {
            return $this->respond()->errorResponse('Acesso negado a este quadro.', 403);
        }

        // Load board with all relationships
        $board->load([
            'columns.tasks',
            'columns.tasks.labels',
            'boardUsers.user',
        ]);

        return $this->respond()->view('boards.show', ['board' => $board], $request);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoardRequest $request, Board $board)
    {
        try {
            $user = $request->user();
            
            // Check if user has permission to update this board
            $userRole = $board->getUserRole($user->id);
            if ($userRole !== RoleInBoard::Owner) {
                return $this->respond()->errorResponse('Apenas o proprietário pode editar este quadro.', 403);
            }

            $board->update($request->only(['name', 'key']));

            return $this->respond()->view('boards.update', ['board' => $board], $request);

        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao atualizar quadro: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Board $board)
    {
        try {
            $user = $request->user();
            
            // Check if user has permission to delete this board
            $userRole = $board->getUserRole($user->id);
            if ($userRole !== RoleInBoard::Owner) {
                return $this->respond()->errorResponse('Apenas o proprietário pode excluir este quadro.', 403);
            }

            $board->delete();

            return $this->respond()->view('boards.destroy', ['message' => 'Quadro excluído com sucesso!'], $request);

        } catch (\Exception $e) {
            return $this->respond()->errorResponse('Erro ao excluir quadro: ' . $e->getMessage(), 500);
        }
    }
}
