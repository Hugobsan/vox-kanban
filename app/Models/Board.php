<?php

namespace App\Models;

use App\Enums\RoleInBoard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Board extends Model
{
    /** @use HasFactory<\Database\Factories\BoardFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'key',
        'slug',
        'next_task_number',
    ];

    protected $casts = [
        'next_task_number' => 'integer',
    ];

    /* Mutators & Accessors */
    public function setNameAttribute($value)
    {
        $trimmedValue = trim($value);
        $this->attributes['name'] = $trimmedValue;
        
        // Generate slug if it doesn't exist
        if (empty($this->attributes['slug'])) {
            $randomString = Str::random(8);
            $this->attributes['slug'] = Str::slug($trimmedValue) . '-' . $randomString;
        }
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst($value)
        );
    }

    protected function key(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => strtoupper(trim($value))
        );
    }

    /* Relationships */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'board_users')
            ->withPivot('role_in_board')
            ->withTimestamps();
    }

    public function boardUsers(): HasMany
    {
        return $this->hasMany(BoardUser::class);
    }

    public function columns(): HasMany
    {
        return $this->hasMany(Column::class)->orderBy('order');
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Column::class);
    }

    public function labels(): HasMany
    {
        return $this->hasMany(Label::class);
    }

    /* Methods */
    public function getUserRole(int $userId): ?RoleInBoard
    {
        $boardUser = $this->boardUsers()->where('user_id', $userId)->first();
        return $boardUser?->role_in_board;
    }

    public function hasUser(int $userId): bool
    {
        return $this->users()->where('user_id', $userId)->exists();
    }

    public function incrementTaskNumber(): int
    {
        $this->increment('next_task_number');
        return $this->next_task_number;
    }

    public function getNextTaskReference(): string
    {
        return $this->key . '-' . $this->next_task_number;
    }
}
