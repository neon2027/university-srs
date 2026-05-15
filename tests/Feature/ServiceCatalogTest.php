<?php

use App\Enums\FieldType;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;

test('service category belongs to an office', function () {
    $office = Office::factory()->create();
    $category = ServiceCategory::factory()->for($office)->create();

    expect($category->office->id)->toBe($office->id);
});

test('service type belongs to a category', function () {
    $category = ServiceCategory::factory()->create();
    $type = ServiceType::factory()->for($category)->create();

    expect($type->serviceCategory->id)->toBe($category->id);
});

test('service type fields are returned ordered by sort_order', function () {
    $type = ServiceType::factory()->create();
    ServiceTypeField::factory()->for($type)->create(['sort_order' => 3]);
    ServiceTypeField::factory()->for($type)->create(['sort_order' => 1]);
    ServiceTypeField::factory()->for($type)->create(['sort_order' => 2]);

    expect($type->fields->pluck('sort_order')->all())->toBe([1, 2, 3]);
});

test('field type enum casts correctly', function () {
    $field = ServiceTypeField::factory()->create(['field_type' => FieldType::Text]);

    expect($field->field_type)->toBe(FieldType::Text);
    expect($field->field_type->value)->toBe('text');
});

test('select field stores options as array', function () {
    $field = ServiceTypeField::factory()->create([
        'field_type' => FieldType::Select,
        'options' => ['Option A', 'Option B', 'Option C'],
    ]);

    expect($field->options)->toHaveCount(3);
    expect($field->options[0])->toBe('Option A');
});
