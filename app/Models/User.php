<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * As atribuições de papéis que pertencem ao usuário.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roleAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RoleUser::class);
    }

    /**
     * Todas as atribuições de papéis (incluindo inativas) que pertencem ao usuário.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function allRoleAssignments(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->hasMany(RoleUser::class)->withInactive();
    }

    /**
     * Os papéis que pertencem ao usuário (através de atribuições de papéis).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany 
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id');
    }

    /**
     * Atribuir um papel ao usuário pelo nome do papel.
     *
     * @param string $roleName
     * @param \Carbon\Carbon|string|null $expiresAt
     * @return bool
     */
    public function assignRole(string $roleName, $expiresAt = null): bool
    {
        $role = Role::findByName($roleName);
        
        if (!$role) {
            return false;
        }

        // Usar firstOrCreate para evitar duplicatas e simplificar o código
        $assignment = $this->allRoleAssignments()->firstOrCreate(
            [
                'role_id' => $role->id,
            ],
            [
                'assigned_at' => now(),
                'revoked_at' => $expiresAt,
            ]
        );

        // Se já existia e estava revogado, reativar
        if ($assignment->wasRecentlyCreated === false && !$assignment->isActive()) {
            $assignment->update([
                'assigned_at' => now(),
                'revoked_at' => $expiresAt,
            ]);
        }

        return true;
    }

    /**
     * Remove um papel do usuário pelo nome do papel.
     *
     * @param string $roleName
     * @return bool
     */
    public function removeRole(string $roleName): bool
    {
        $role = Role::findByName($roleName);
        
        if (!$role) {
            return false;
        }

        $assignment = $this->roleAssignments() // Já pega apenas ativos
            ->where('role_id', $role->id)
            ->first();

        if ($assignment) {
            return $assignment->revoke();
        }

        return true;
    }

    /**
     * Verifica se o usuário tem um papel específico.
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    /**
     * Obtém os nomes de todas as roles ativas do usuário.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoleNames(): \Illuminate\Support\Collection
    {
        return $this->roles()->pluck('name');
    }

    /**
     * Os boards que pertencem ao usuário.
     */
    public function boards(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Board::class, 'board_users')
            ->withPivot('role_in_board')
            ->withTimestamps();
    }

    /**
     * As associações do usuário aos boards.
     */
    public function boardUsers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BoardUser::class);
    }

    /**
     * As tasks atribuídas ao usuário.
     */
    public function assignedTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class, 'assigned_user_id');
    }
}
