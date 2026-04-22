<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ApprovalLog extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (ApprovalLog $model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'submission_id',
        'approver_id',
        'action',
        'notes',
    ];

    protected $appends = [
        'approver_name',
        'approver_role',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected function approverName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approver?->name
        );
    }

    protected function approverRole(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->approver?->role?->name
        );
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function isApproval(): bool
    {
        return $this->action === 'approve';
    }

    public function isRejection(): bool
    {
        return $this->action === 'reject';
    }
}
