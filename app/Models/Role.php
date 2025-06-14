<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    /** @use HasFactory<\Database\Factories\RoleFactory> */
    use HasFactory;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * As atribuições de papéis para este papel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roleAssignments()
    {
        return $this->hasMany(RoleUser::class);
    }

    /**
     * As atribuições de papéis ativas para este papel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeRoleAssignments()
    {
        return $this->roleAssignments(); // Global scope já aplica o filtro ativo
    }

    /**
     * Todas as atribuições de papéis (incluindo inativas) para este papel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function allRoleAssignments()
    {
        return $this->hasMany(RoleUser::class)->withInactive();
    }

    /**
     * Os usuários que têm este papel (através de atribuições ativas).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
        // Global scope já aplica o filtro ativo automaticamente
    }

    /**
     * Encontrar papel pelo nome.
     *
     * @param string $name
     * @return static|null
     */
    public static function findByName(string $name): ?static
    {
        return static::where('name', $name)->first();
    }
}
