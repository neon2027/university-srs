<?php

namespace App\Models;

use Database\Factories\ServiceTypeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['service_category_id', 'name', 'slug', 'description', 'sla_days', 'is_active', 'sort_order'])]
class ServiceType extends Model
{
    /** @use HasFactory<ServiceTypeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (ServiceType $type) {
            $type->slug ??= Str::slug($type->name);
        });
    }

    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceTypeField::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
