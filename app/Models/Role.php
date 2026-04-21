<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = ['name', 'next_role_id'];

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
