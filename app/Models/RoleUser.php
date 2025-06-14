<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleUser extends Model
{
    /** @use HasFactory<\Database\Factories\RoleUserFactory> */
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'role_user';

    /**
     * Indica se o modelo deve ter timestamps.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Os atributos que podem ser atribuídos em massa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'role_id',
        'assigned_at',
        'revoked_at',
    ];

    /**
     * Os atributos que devem ser convertidos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assigned_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * O método "booted" do modelo.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where(function ($query) {
                $query->whereNull('revoked_at')
                      ->orWhere('revoked_at', '>', now());
            });
        });
    }

    /**
     * Scope para obter todas as atribuições de papéis incluindo inativas.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeWithInactive(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active');
    }

    /**
     * Obtém o usuário que possui a atribuição de papel.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtém o papel que foi atribuído.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Verifica se a atribuição de papel está ativa.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->revoked_at === null || $this->revoked_at->isFuture();
    }

    /**
     * Revoga a atribuição de papel.
     *
     * @return bool
     */
    public function revoke(): bool
    {
        return $this->update(['revoked_at' => now()]);
    }

    /**
     * Reativa a atribuição de papel.
     *
     * @param \Carbon\Carbon|string|null $expiresAt
     * @return bool
     */
    public function reactivate($expiresAt = null): bool
    {
        return $this->update([
            'assigned_at' => now(),
            'revoked_at' => $expiresAt,
        ]);
    }

    /**
     * Define data de expiração para a atribuição de papel.
     *
     * @param \Carbon\Carbon|string $date
     * @return bool
     */
    public function setExpiration($date): bool
    {
        return $this->update(['revoked_at' => $date]);
    }
}
