<?php

namespace App\Models;

use Database\Factories\ServiceCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['office_id', 'name', 'slug', 'description', 'is_active', 'sort_order'])]
class ServiceCategory extends Model
{
    /** @use HasFactory<ServiceCategoryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (ServiceCategory $category) {
            $category->slug ??= Str::slug($category->name);
        });
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function serviceTypes(): HasMany
    {
        return $this->hasMany(ServiceType::class)->orderBy('sort_order');
    }

    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
