<?php

namespace App\Models;

use App\Enums\FieldType;
use Database\Factories\ServiceTypeFieldFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['service_type_id', 'label', 'field_type', 'options', 'is_required', 'sort_order'])]
class ServiceTypeField extends Model
{
    /** @use HasFactory<ServiceTypeFieldFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'field_type' => FieldType::class,
            'options' => 'array',
            'is_required' => 'boolean',
        ];
    }

    public function serviceType(): BelongsTo
    {
        return $this->belongsTo(ServiceType::class);
    }
}
