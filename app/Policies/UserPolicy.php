<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determina se o usuário pode visualizar qualquer usuário.
     */
    public function viewAny(User $user): bool
    {
        // Apenas admins podem ver lista de usuários
        return $user->hasRole('admin');
    }

    /**
     * Determina se o usuário pode visualizar o usuário especificado.
     */
    public function view(User $user, User $model): bool
    {
        // Admin pode ver qualquer usuário
        // Usuário pode ver apenas a si mesmo
        return $user->hasRole('admin') || $user->id === $model->id;
    }

    /**
     * Determina se o usuário pode criar usuários.
     */
    public function create(User $user): bool
    {
        // Admins podem criar usuários
        // Para cadastro público, verificação será feita no controller/request
        return $user->hasRole('admin');
    }

    /**
     * Determina se o usuário pode atualizar o usuário especificado.
     */
    public function update(User $user, User $model): bool
    {
        // Admin pode editar qualquer usuário
        // Usuário pode editar apenas a si mesmo
        return $user->hasRole('admin') || $user->id === $model->id;
    }

    /**
     * Determina se o usuário pode deletar o usuário especificado.
     */
    public function delete(User $user, User $model): bool
    {
        // Apenas admins podem deletar usuários
        // Admin não pode deletar a si mesmo
        return $user->hasRole('admin') && $user->id !== $model->id;
    }

    /**
     * Determina se o usuário pode restaurar o usuário especificado.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determina se o usuário pode deletar permanentemente o usuário especificado.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('admin') && $user->id !== $model->id;
    }

    /**
     * Determina se o usuário pode atribuir roles.
     */
    public function assignRoles(User $user, User $model): bool
    {
        // Apenas admins podem atribuir roles
        return $user->hasRole('admin');
    }

    /**
     * Determina se o usuário pode remover roles.
     */
    public function removeRoles(User $user, User $model): bool
    {
        // Apenas admins podem remover roles
        return $user->hasRole('admin');
    }

    /**
     * Determina quais roles o usuário pode atribuir.
     */
    public function canAssignRole(User $user, string $roleName): bool
    {
        // Apenas admins podem atribuir qualquer role
        if ($user->hasRole('admin')) {
            return true;
        }

        // Usuários comuns só podem ter role 'user' ou nenhuma
        return in_array($roleName, ['user']);
    }

    /**
     * Determina se o usuário pode atualizar informações sensíveis (email, password).
     */
    public function updateSensitiveInfo(User $user, User $model): bool
    {
        // Admin pode atualizar qualquer usuário
        // Usuário pode atualizar apenas a si mesmo
        return $user->hasRole('admin') || $user->id === $model->id;
    }
}
