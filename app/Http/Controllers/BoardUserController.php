<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteBoardUserRequest;
use App\Http\Requests\UpdateBoardUserRequest;
use App\Models\Board;
use App\Models\BoardUser;
use App\Models\User;
use App\Notifications\BoardInvitationToExistingUser;
use App\Notifications\BoardInvitationToNewUser;
use App\Support\SmartResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class BoardUserController extends Controller
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
    public function store(InviteBoardUserRequest $request)
    {
        $board = Board::findOrFail($request->board_id);
        $this->authorize('update', $board);

        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->first();
            
            if ($user) {
                $existingBoardUser = BoardUser::where('board_id', $board->id)
                    ->where('user_id', $user->id)
                    ->first();
                
                if ($existingBoardUser) {
                    return $this->respond()->errorResponse('Usuário já é membro deste quadro.', 422);
                }
                
                $boardUser = BoardUser::create([
                    'board_id' => $board->id,
                    'user_id' => $user->id,
                    'role_in_board' => $request->role_in_board,
                ]);
            } else {
                Notification::route('mail', $request->email)
                    ->notify(new BoardInvitationToNewUser($board, $request->role_in_board, $request->email));
                $boardUser = null;
            }

            DB::commit();
            
            $message = $user 
                ? 'Convite enviado com sucesso! O usuário foi adicionado ao quadro.'
                : 'Convite enviado com sucesso! Um email foi enviado para o usuário se registrar.';
                
            return $this->respond()->successResponse($boardUser, $message, 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respond()->errorResponse('Erro ao enviar convite: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(BoardUser $boardUser)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BoardUser $boardUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBoardUserRequest $request, BoardUser $boardUser)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BoardUser $boardUser)
    {
        //
    }
}
