<?php

namespace Database\Seeders;

use App\Enums\FieldType;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use Illuminate\Database\Seeder;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Information Technology Office' => [
                [
                    'name' => 'Account and Access',
                    'slug' => 'ito-account-and-access',
                    'description' => 'Login, account access, and system permission requests.',
                    'sort_order' => 1,
                    'types' => [
                        [
                            'name' => 'Password or Account Access Help',
                            'slug' => 'ito-password-account-access-help',
                            'description' => 'Request help with university system sign-in or account access.',
                            'sla_days' => 2,
                            'sort_order' => 1,
                            'fields' => [
                                ['label' => 'System or application', 'field_type' => FieldType::Select, 'options' => ['Student Portal', 'Email', 'LMS', 'Enrollment System'], 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'Issue details', 'field_type' => FieldType::Textarea, 'is_required' => true, 'sort_order' => 2],
                            ],
                        ],
                        [
                            'name' => 'Software Installation Request',
                            'slug' => 'ito-software-installation-request',
                            'description' => 'Request installation of approved software for academic use.',
                            'sla_days' => 3,
                            'sort_order' => 2,
                            'fields' => [
                                ['label' => 'Software name', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'Device or laboratory location', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 2],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Hardware and Network',
                    'slug' => 'ito-hardware-and-network',
                    'description' => 'Computer, printer, Wi-Fi, and network connectivity concerns.',
                    'sort_order' => 2,
                    'types' => [
                        [
                            'name' => 'Wi-Fi or Internet Connectivity Issue',
                            'slug' => 'ito-wifi-internet-connectivity-issue',
                            'description' => 'Report unstable or unavailable internet connectivity.',
                            'sla_days' => 2,
                            'sort_order' => 1,
                            'fields' => [
                                ['label' => 'Affected location', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'When did it start?', 'field_type' => FieldType::Date, 'is_required' => false, 'sort_order' => 2],
                            ],
                        ],
                    ],
                ],
            ],
            'Registrar Office' => [
                [
                    'name' => 'Academic Records',
                    'slug' => 'registrar-academic-records',
                    'description' => 'Requests related to grades, records, and academic documents.',
                    'sort_order' => 1,
                    'types' => [
                        [
                            'name' => 'Transcript of Records Request',
                            'slug' => 'registrar-transcript-of-records-request',
                            'description' => 'Request processing or follow-up for transcript of records.',
                            'sla_days' => 7,
                            'sort_order' => 1,
                            'fields' => [
                                ['label' => 'Purpose', 'field_type' => FieldType::Select, 'options' => ['Employment', 'Scholarship', 'Transfer', 'Board Exam', 'Other'], 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'Number of copies', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 2],
                            ],
                        ],
                        [
                            'name' => 'Grade Correction Concern',
                            'slug' => 'registrar-grade-correction-concern',
                            'description' => 'Report a possible issue with a posted grade.',
                            'sla_days' => 5,
                            'sort_order' => 2,
                            'fields' => [
                                ['label' => 'Course code', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'Term or semester', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 2],
                                ['label' => 'Supporting document', 'field_type' => FieldType::File, 'is_required' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],
            ],
            'Student Affairs Office' => [
                [
                    'name' => 'Student Services',
                    'slug' => 'sao-student-services',
                    'description' => 'Support for student activities, IDs, and general student concerns.',
                    'sort_order' => 1,
                    'types' => [
                        [
                            'name' => 'Student ID Concern',
                            'slug' => 'sao-student-id-concern',
                            'description' => 'Report a lost, damaged, or incorrect student ID.',
                            'sla_days' => 4,
                            'sort_order' => 1,
                            'fields' => [
                                ['label' => 'Concern type', 'field_type' => FieldType::Select, 'options' => ['Lost ID', 'Damaged ID', 'Incorrect Details', 'Replacement Follow-up'], 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'Additional details', 'field_type' => FieldType::Textarea, 'is_required' => false, 'sort_order' => 2],
                            ],
                        ],
                        [
                            'name' => 'Student Organization Activity Request',
                            'slug' => 'sao-student-organization-activity-request',
                            'description' => 'Submit a student organization activity request for review.',
                            'sla_days' => 5,
                            'sort_order' => 2,
                            'fields' => [
                                ['label' => 'Organization name', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'Activity date', 'field_type' => FieldType::Date, 'is_required' => true, 'sort_order' => 2],
                                ['label' => 'Activity proposal', 'field_type' => FieldType::File, 'is_required' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],
            ],
            'Finance Office' => [
                [
                    'name' => 'Payments and Billing',
                    'slug' => 'finance-payments-and-billing',
                    'description' => 'Payment validation, billing, and assessment concerns.',
                    'sort_order' => 1,
                    'types' => [
                        [
                            'name' => 'Payment Posting Concern',
                            'slug' => 'finance-payment-posting-concern',
                            'description' => 'Ask for help when a payment is not reflected in your account.',
                            'sla_days' => 3,
                            'sort_order' => 1,
                            'fields' => [
                                ['label' => 'Payment reference number', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'Payment date', 'field_type' => FieldType::Date, 'is_required' => true, 'sort_order' => 2],
                                ['label' => 'Proof of payment', 'field_type' => FieldType::File, 'is_required' => false, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],
            ],
            'Physical Plant Office' => [
                [
                    'name' => 'Facilities Maintenance',
                    'slug' => 'ppo-facilities-maintenance',
                    'description' => 'Classroom, office, electrical, plumbing, and facility repair requests.',
                    'sort_order' => 1,
                    'types' => [
                        [
                            'name' => 'Classroom or Facility Repair',
                            'slug' => 'ppo-classroom-facility-repair',
                            'description' => 'Report facilities that need repair or inspection.',
                            'sla_days' => 5,
                            'sort_order' => 1,
                            'fields' => [
                                ['label' => 'Building and room', 'field_type' => FieldType::Text, 'is_required' => true, 'sort_order' => 1],
                                ['label' => 'Repair type', 'field_type' => FieldType::Select, 'options' => ['Electrical', 'Plumbing', 'Furniture', 'Air Conditioning', 'Other'], 'is_required' => true, 'sort_order' => 2],
                                ['label' => 'Issue description', 'field_type' => FieldType::Textarea, 'is_required' => true, 'sort_order' => 3],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($catalog as $officeName => $categories) {
            $office = Office::where('name', $officeName)->first();

            if (! $office) {
                continue;
            }

            foreach ($categories as $categoryData) {
                $types = $categoryData['types'];
                unset($categoryData['types']);

                $category = ServiceCategory::updateOrCreate(
                    ['slug' => $categoryData['slug']],
                    [...$categoryData, 'office_id' => $office->id, 'is_active' => true],
                );

                foreach ($types as $typeData) {
                    $fields = $typeData['fields'];
                    unset($typeData['fields']);

                    $type = ServiceType::updateOrCreate(
                        ['slug' => $typeData['slug']],
                        [...$typeData, 'service_category_id' => $category->id, 'is_active' => true],
                    );

                    foreach ($fields as $fieldData) {
                        ServiceTypeField::updateOrCreate(
                            [
                                'service_type_id' => $type->id,
                                'label' => $fieldData['label'],
                            ],
                            $fieldData,
                        );
                    }
                }
            }
        }
    }
}
