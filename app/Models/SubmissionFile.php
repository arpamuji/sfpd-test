<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SubmissionFile extends Model
{
    use HasFactory;

    public $incrementing = false;

    public $keyType = 'string';

    protected static function booted(): void
    {
        static::creating(function (SubmissionFile $model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'submission_id',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
    ];

    protected function casts(): array
    {
        return [];
    }

    protected $appends = [
        'url',
        'name',
    ];

    /**
     * Get the URL for the file.
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => Storage::disk('public')->url($this->file_path)
        );
    }

    /**
     * Get the display name for the file.
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->file_name
        );
    }

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
