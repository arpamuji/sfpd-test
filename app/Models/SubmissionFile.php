<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFile extends Model
{
    public $incrementing = false;
    public $keyType = 'string';

    protected $fillable = [
        'submission_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function isPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->file_type, 'image/');
    }
}
