<?php

namespace App\Policies;

use App\Models\Board;
use App\Models\User;
use App\Enums\RoleInBoard;

/**
 * BoardPolicy - Centraliza todas as autorizações relacionadas ao Board
 * 
 * Esta policy deve ser usada para verificações de:
 * - Colunas: $user->can('manageColumns', $board)
 * - Labels: $user->can('manageLabels', $board) 
 * - Tasks: $user->can('createTasks', $board), $user->can('updateTasks', $board), etc.
 * - Usuários do Board: $user->can('manageUsers', $board)
 * 
 * Para tasks específicas com regras especiais (ex: viewer editando própria task):
 * Fazer verificação adicional no controller após autorizar no board.
 */
class BoardPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Admins podem ver todos os boards, usuários comuns apenas os seus
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Board $board): bool
    {
        // Admin pode ver qualquer board
        if ($user->isAdmin()) {
            return true;
        }
        
        // Usuário pode ver se estiver associado ao board
        return $board->hasUser($user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Usuários autenticados podem criar boards
        return $user->isUser() || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Board $board): bool
    {
        // Admin pode editar qualquer board
        if ($user->isAdmin()) {
            return true;
        }
        
        // Apenas owners e editors podem editar o board
        $role = $board->getUserRole($user->id);
        return $role && in_array($role, [RoleInBoard::Owner, RoleInBoard::Editor]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Board $board): bool
    {
        // Admin pode deletar qualquer board
        if ($user->isAdmin()) {
            return true;
        }
        
        // Apenas owners podem deletar o board
        $role = $board->getUserRole($user->id);
        return $role === RoleInBoard::Owner;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Board $board): bool
    {
        // Mesmas regras do delete
        return $this->delete($user, $board);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Board $board): bool
    {
        // Apenas admins podem deletar permanentemente
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can manage users in the board.
     */
    public function manageUsers(User $user, Board $board): bool
    {
        // Admin pode gerenciar usuários em qualquer board
        if ($user->isAdmin()) {
            return true;
        }
        
        // Apenas owners podem gerenciar usuários
        $role = $board->getUserRole($user->id);
        return $role === RoleInBoard::Owner;
    }

    /**
     * Determine whether the user can manage columns in the board.
     */
    public function manageColumns(User $user, Board $board): bool
    {
        return $this->update($user, $board);
    }

    /**
     * Determine whether the user can manage labels in the board.
     */
    public function manageLabels(User $user, Board $board): bool
    {
        return $this->update($user, $board);
    }

    /**
     * Determine whether the user can create tasks in the board.
     */
    public function createTasks(User $user, Board $board): bool
    {
        return $this->update($user, $board);
    }

    /**
     * Determine whether the user can update tasks in the board.
     */
    public function updateTasks(User $user, Board $board): bool
    {
        // Admin pode editar qualquer task
        if ($user->isAdmin()) {
            return true;
        }
        
        $role = $board->getUserRole($user->id);
        
        // Owners e editors podem editar qualquer task
        if ($role && in_array($role, [RoleInBoard::Owner, RoleInBoard::Editor])) {
            return true;
        }
        
        // Viewers podem editar apenas tasks atribuídas a eles (será verificado individualmente)
        return $role === RoleInBoard::Viewer;
    }

    /**
     * Determine whether the user can assign tasks in the board.
     */
    public function assignTasks(User $user, Board $board): bool
    {
        // Admin pode atribuir qualquer task
        if ($user->isAdmin()) {
            return true;
        }
        
        // Apenas owners e editors podem atribuir tasks
        $role = $board->getUserRole($user->id);
        return $role && in_array($role, [RoleInBoard::Owner, RoleInBoard::Editor]);
    }

    /**
     * Determine whether the user can delete tasks in the board.
     */
    public function deleteTasks(User $user, Board $board): bool
    {
        // Admin pode deletar qualquer task
        if ($user->isAdmin()) {
            return true;
        }
        
        // Apenas owners e editors podem deletar tasks
        $role = $board->getUserRole($user->id);
        return $role && in_array($role, [RoleInBoard::Owner, RoleInBoard::Editor]);
    }
}
