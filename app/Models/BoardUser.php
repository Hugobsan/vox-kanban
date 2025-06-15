<?php

namespace App\Models;

use App\Enums\RoleInBoard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class BoardUser extends Model
{
    /** @use HasFactory<\Database\Factories\BoardUserFactory> */
    use HasFactory;

    protected $fillable = [
        'board_id',
        'user_id',
        'role_in_board',
    ];

    protected $casts = [
        'role_in_board' => RoleInBoard::class,
    ];

    /* Relationships */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* Scopes */
    public function scopeByRole(Builder $query, RoleInBoard $role): Builder
    {
        return $query->where('role_in_board', $role->value);
    }

    public function scopeOwners(Builder $query): Builder
    {
        return $query->where('role_in_board', RoleInBoard::Owner->value);
    }

    public function scopeEditors(Builder $query): Builder
    {
        return $query->where('role_in_board', RoleInBoard::Editor->value);
    }

    public function scopeViewers(Builder $query): Builder
    {
        return $query->where('role_in_board', RoleInBoard::Viewer->value);
    }

    /* Methods */
    public function isOwner(): bool
    {
        return $this->role_in_board === RoleInBoard::Owner;
    }

    public function isEditor(): bool
    {
        return $this->role_in_board === RoleInBoard::Editor;
    }

    public function isViewer(): bool
    {
        return $this->role_in_board === RoleInBoard::Viewer;
    }
}
