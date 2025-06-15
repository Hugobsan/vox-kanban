<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Column extends Model
{
    /** @use HasFactory<\Database\Factories\ColumnFactory> */
    use HasFactory;

    protected $fillable = [
        'board_id',
        'name',
        'color',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /* Mutators & Accessors */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => trim($value)
        );
    }

    protected function color(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtoupper($value)
        );
    }

    /* Relationships */
    public function board(): BelongsTo
    {
        return $this->belongsTo(Board::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('order');
    }

    public function activeTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNull('deleted_at')->orderBy('order');
    }

    public function completedTasks(): HasMany
    {
        return $this->hasMany(Task::class)->whereNotNull('completed_at')->orderBy('order');
    }

    /* Scopes */
    public function scopeByBoard(Builder $query, int $boardId): Builder
    {
        return $query->where('board_id', $boardId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }

    public function scopeWithTasksCount(Builder $query): Builder
    {
        return $query->withCount(['tasks', 'activeTasks', 'completedTasks']);
    }

    /* Methods */
    public function getNextTaskOrder(): int
    {
        return $this->activeTasks()->max('order') + 1;
    }

    public function moveTasksUp(int $fromOrder): void
    {
        $this->tasks()
            ->where('order', '>', $fromOrder)
            ->decrement('order');
    }

    public function moveTasksDown(int $fromOrder, int $toOrder): void
    {
        $this->tasks()
            ->whereBetween('order', [$fromOrder, $toOrder])
            ->increment('order');
    }
}
