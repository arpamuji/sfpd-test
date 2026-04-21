<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = ['id', 'name', 'next_role_id'];

    protected static function booted(): void
    {
        static::creating(function (Role $model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function nextRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'next_role_id');
    }

    public function submissionsAtThisLevel(): HasMany
    {
        return $this->hasMany(Submission::class, 'current_role_id');
    }

    public function isFinalApprover(): bool
    {
        return $this->next_role_id === null;
    }
}
