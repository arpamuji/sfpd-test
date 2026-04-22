<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $incrementing = false;

    public $keyType = 'string';

    protected $fillable = [
        'id',
        'role_id',
        'name',
        'email',
        'password',
        'google2fa_secret',
        'google2fa_enabled',
    ];

    protected static function booted(): void
    {
        static::creating(function (User $model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $hidden = ['password', 'google2fa_secret'];

    protected function casts(): array
    {
        return [
            'google2fa_enabled' => 'boolean',
            'google2fa_secret' => 'encrypted',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'requestor_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(ApprovalLog::class, 'approver_id');
    }
}
