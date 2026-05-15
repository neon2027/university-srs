<?php

use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use Database\Seeders\OfficeSeeder;
use Database\Seeders\ServiceCatalogSeeder;

test('service catalog seeder creates sample request categories and types', function () {
    $this->seed([
        OfficeSeeder::class,
        ServiceCatalogSeeder::class,
    ]);

    expect(Office::whereHas('serviceCategories')->count())->toBeGreaterThan(0)
        ->and(ServiceCategory::active()->count())->toBeGreaterThan(0)
        ->and(ServiceType::active()->count())->toBeGreaterThan(0)
        ->and(ServiceTypeField::count())->toBeGreaterThan(0);
});
