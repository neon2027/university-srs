<?php

namespace App\Models;

use Database\Factories\CannedResponseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['office_id', 'title', 'body', 'created_by', 'is_active'])]
class CannedResponse extends Model
{
    /** @use HasFactory<CannedResponseFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    public function scopeForOffice(Builder $query, int $officeId): void
    {
        $query->where(function (Builder $q) use ($officeId) {
            $q->where('office_id', $officeId)->orWhereNull('office_id');
        });
    }
}
