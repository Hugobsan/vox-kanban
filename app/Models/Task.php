<?php

namespace App\Models;

use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([TaskObserver::class])]
class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'column_id',
        'reference',
        'title',
        'description',
        'order',
        'assigned_user_id',
        'due_date',
        'completed_at',
    ];

    protected $casts = [
        'order' => 'integer',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /* Mutators & Accessors */
    protected function title(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value),
            set: fn ($value) => trim($value)
        );
    }

    protected function description(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value ? trim($value) : null
        );
    }

    protected function isCompleted(): Attribute
    {
        return Attribute::make(
            get: fn () => !is_null($this->completed_at)
        );
    }

    protected function isOverdue(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->due_date && $this->due_date->isPast() && !$this->is_completed
        );
    }

    /* Relationships */
    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function board(): HasOneThrough
    {
        return $this->hasOneThrough(Board::class, Column::class, 'id', 'id', 'column_id', 'board_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function labels(): BelongsToMany
    {
        return $this->belongsToMany(Label::class, 'label_task')->withTimestamps();
    }

    /* Scopes */
    public function scopeByColumn(Builder $query, int $columnId): Builder
    {
        return $query->where('column_id', $columnId);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeIncomplete(Builder $query): Builder
    {
        return $query->whereNull('completed_at');
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
                    ->whereNull('completed_at');
    }

    /* Methods */
    public function markAsCompleted(): bool
    {
        $this->completed_at = now();
        return $this->save();
    }

    public function markAsIncomplete(): bool
    {
        $this->completed_at = null;
        return $this->save();
    }

    public function moveToColumn(int $columnId, int $order = null): bool
    {
        $this->column_id = $columnId;
        
        if ($order !== null) {
            $this->order = $order;
        } else {
            $newColumn = Column::find($columnId);
            $this->order = $newColumn->getNextTaskOrder();
        }
        
        return $this->save();
    }
}
