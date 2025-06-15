<?php

namespace App\Policies;

use App\Models\BoardUser;
use App\Models\User;
use App\Enums\RoleInBoard;

class BoardUserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BoardUser $boardUser): bool
    {
        // Admin pode ver qualquer associação
        if ($user->isAdmin()) {
            return true;
        }
        
        // Usuário pode ver se for do mesmo board
        return $boardUser->board->hasUser($user->id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isUser();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BoardUser $boardUser): bool
    {
        // Admin pode editar qualquer associação
        if ($user->isAdmin()) {
            return true;
        }
        
        // Apenas owners podem alterar roles de usuários
        $role = $boardUser->board->getUserRole($user->id);
        return $role === RoleInBoard::Owner;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BoardUser $boardUser): bool
    {
        // Admin pode remover qualquer associação
        if ($user->isAdmin()) {
            return true;
        }
        
        // Owner pode remover qualquer usuário
        $role = $boardUser->board->getUserRole($user->id);
        if ($role === RoleInBoard::Owner) {
            return true;
        }
        
        // Usuário pode remover a si mesmo do board
        return $boardUser->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BoardUser $boardUser): bool
    {
        // Apenas admins e owners podem restaurar
        if ($user->isAdmin()) {
            return true;
        }
        
        $role = $boardUser->board->getUserRole($user->id);
        return $role === RoleInBoard::Owner;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BoardUser $boardUser): bool
    {
        return $user->isAdmin();
    }
}
