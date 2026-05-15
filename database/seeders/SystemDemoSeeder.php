<?php

namespace Database\Seeders;

use App\Enums\CreditType;
use App\Enums\EventType;
use App\Enums\FieldType;
use App\Enums\OnboardingStatus;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\CannedResponse;
use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use App\Models\TicketMessage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SystemDemoSeeder extends Seeder
{
    private array $demoFiles = [];

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $this->prepareDemoFiles();

        $offices = $this->seedOffices();
        $users = $this->seedUsers($offices);
        $services = $this->seedServiceCatalog($offices);

        $this->seedCannedResponses($offices, $users);
        $this->seedTickets($offices, $users, $services);
    }

    private function prepareDemoFiles(): void
    {
        $files = [
            'charters/icto-citizen-charter.pdf' => "iBUConnect demo file\nInformation Technology Office Citizen Charter\n",
            'charters/registrar-citizen-charter.pdf' => "iBUConnect demo file\nUniversity Registrar Citizen Charter\n",
            'charters/finance-citizen-charter.pdf' => "iBUConnect demo file\nFinance Office Citizen Charter\n",
            'charters/ppo-citizen-charter.pdf' => "iBUConnect demo file\nPhysical Plant Office Citizen Charter\n",
            'work-instructions/bu-email-account.pdf' => "iBUConnect demo file\nWork instruction: BU Email Account Request\n",
            'work-instructions/transcript-request.pdf' => "iBUConnect demo file\nWork instruction: Transcript of Records Request\n",
            'work-instructions/payment-certification.pdf' => "iBUConnect demo file\nWork instruction: Payment Certification Request\n",
            'work-instructions/maintenance-request.pdf' => "iBUConnect demo file\nWork instruction: Campus Maintenance Request\n",
            'attachments/student-id-front.jpg' => "FAKE-JPEG-DATA: Student ID front image for demo only.\n",
            'attachments/registration-form.pdf' => "iBUConnect demo attachment\nRegistration form\n",
            'attachments/proof-of-payment.pdf' => "iBUConnect demo attachment\nProof of payment\n",
            'attachments/clearance-slip.pdf' => "iBUConnect demo attachment\nClearance slip uploaded through public tracker\n",
            'attachments/lab-photo.jpg' => "FAKE-JPEG-DATA: Computer laboratory photo for demo only.\n",
            'attachments/request-summary.csv' => "ticket,office,status\nDEMO-ITO-26-0001,ICTO,pending\n",
        ];

        foreach ($files as $path => $contents) {
            $storagePath = "demo/{$path}";
            Storage::disk('local')->put($storagePath, $contents);
            $this->demoFiles[$path] = [
                'path' => $storagePath,
                'size' => strlen($contents),
            ];
        }
    }

    private function seedOffices(): array
    {
        $offices = [
            'icto' => [
                'name' => 'Information Technology Office',
                'description' => 'Handles university account access, campus network support, software assistance, and technology service requests.',
                'email' => 'icto@bicol-u.edu.ph',
                'citizen_charter' => $this->demoFiles['charters/icto-citizen-charter.pdf']['path'],
                'sort_order' => 1,
            ],
            'registrar' => [
                'name' => 'University Registrar',
                'description' => 'Processes academic records, enrollment documents, certification requests, and graduation-related services.',
                'email' => 'registrar@bicol-u.edu.ph',
                'citizen_charter' => $this->demoFiles['charters/registrar-citizen-charter.pdf']['path'],
                'sort_order' => 2,
            ],
            'finance' => [
                'name' => 'Finance Office',
                'description' => 'Supports payment verification, scholarship billing, official receipts, and financial certifications.',
                'email' => 'finance@bicol-u.edu.ph',
                'citizen_charter' => $this->demoFiles['charters/finance-citizen-charter.pdf']['path'],
                'sort_order' => 3,
            ],
            'physical-plant' => [
                'name' => 'Physical Plant Office',
                'description' => 'Coordinates facilities maintenance, repair requests, room concerns, and campus infrastructure support.',
                'email' => 'physicalplant@bicol-u.edu.ph',
                'citizen_charter' => $this->demoFiles['charters/ppo-citizen-charter.pdf']['path'],
                'sort_order' => 4,
            ],
        ];

        return collect($offices)
            ->mapWithKeys(fn (array $data, string $slug) => [
                $slug => Office::updateOrCreate(
                    ['slug' => $slug],
                    [...$data, 'slug' => $slug, 'is_active' => true],
                ),
            ])
            ->all();
    }

    private function seedUsers(array $offices): array
    {
        $users = [
            'super' => $this->upsertUser('Demo Super Admin', 'demo.super@bicol-u.edu.ph', 'super_admin'),
            'icto_admin' => $this->upsertUser('Iris Mendoza', 'icto.admin@bicol-u.edu.ph', 'office_admin', $offices['icto']),
            'icto_staff' => $this->upsertUser('Evan Lim', 'icto.staff@bicol-u.edu.ph', 'staff', $offices['icto']),
            'registrar_admin' => $this->upsertUser('Rhea Villanueva', 'registrar.admin@bicol-u.edu.ph', 'office_admin', $offices['registrar']),
            'registrar_staff' => $this->upsertUser('Marco Santos', 'registrar.staff@bicol-u.edu.ph', 'staff', $offices['registrar']),
            'finance_staff' => $this->upsertUser('Liza Bautista', 'finance.staff@bicol-u.edu.ph', 'staff', $offices['finance']),
            'ppo_staff' => $this->upsertUser('Noel Fernandez', 'ppo.staff@bicol-u.edu.ph', 'staff', $offices['physical-plant']),
            'ana' => $this->upsertUser('Ana Reyes', 'ana.reyes@student.bicol-u.edu.ph', 'student'),
            'juan' => $this->upsertUser('Juan Santos', 'juan.santos@student.bicol-u.edu.ph', 'student'),
            'maria' => $this->upsertUser('Maria Dela Cruz', 'maria.delacruz@student.bicol-u.edu.ph', 'student'),
            'carlo' => $this->upsertUser('Carlo Garcia', 'carlo.garcia@student.bicol-u.edu.ph', 'student'),
            'pending_employee' => $this->upsertUser('Bianca Mercado', 'bianca.mercado@bicol-u.edu.ph', 'student', null, [
                'onboarding_status' => OnboardingStatus::PendingEmployee,
                'pending_office_id' => $offices['registrar']->id,
            ]),
            'rejected_employee' => $this->upsertUser('Paolo Rivera', 'paolo.rivera@bicol-u.edu.ph', 'student', null, [
                'onboarding_status' => OnboardingStatus::Rejected,
                'pending_office_id' => null,
            ]),
        ];

        $users['super']->offices()->syncWithoutDetaching([
            $offices['icto']->id => ['is_primary' => true],
            $offices['registrar']->id => ['is_primary' => false],
            $offices['finance']->id => ['is_primary' => false],
            $offices['physical-plant']->id => ['is_primary' => false],
        ]);

        return $users;
    }

    private function upsertUser(string $name, string $email, string $role, ?Office $office = null, array $overrides = []): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'google_id' => 'demo-'.Str::slug($email),
                'avatar' => 'https://api.dicebear.com/9.x/initials/svg?seed='.rawurlencode($name),
                'onboarding_status' => null,
                'pending_office_id' => null,
                'onboarding_completed_at' => now()->subDays(14),
                ...$overrides,
            ],
        );

        $user->syncRoles([$role]);

        if ($office) {
            $user->offices()->syncWithoutDetaching([$office->id => ['is_primary' => true]]);
        }

        return $user;
    }

    private function seedServiceCatalog(array $offices): array
    {
        $catalog = [
            'icto' => [
                'category' => ['slug' => 'demo-technology-support', 'name' => 'Technology Support', 'description' => 'Accounts, campus systems, and device assistance.'],
                'services' => [
                    'bu-email-account' => [
                        'name' => 'BU Email Account Request',
                        'description' => 'Create or restore a university Google Workspace account.',
                        'work_instruction' => $this->demoFiles['work-instructions/bu-email-account.pdf']['path'],
                        'sla_days' => 2,
                        'fields' => [
                            ['Student ID Number', FieldType::Text, true],
                            ['Account Concern', FieldType::Select, true, ['New account', 'Password reset', 'Account recovery']],
                            ['Supporting Screenshot', FieldType::File, false],
                        ],
                    ],
                    'network-access' => [
                        'name' => 'Campus Wi-Fi Access',
                        'description' => 'Request campus network access or report Wi-Fi connectivity issues.',
                        'work_instruction' => null,
                        'sla_days' => 3,
                        'fields' => [
                            ['Campus Location', FieldType::Text, true],
                            ['Device Type', FieldType::Select, true, ['Laptop', 'Phone', 'Tablet', 'Desktop']],
                            ['Issue Details', FieldType::Textarea, true],
                        ],
                    ],
                ],
            ],
            'registrar' => [
                'category' => ['slug' => 'demo-academic-records', 'name' => 'Academic Records', 'description' => 'Transcript, certification, enrollment, and student record requests.'],
                'services' => [
                    'transcript-request' => [
                        'name' => 'Transcript of Records',
                        'description' => 'Request an official or evaluation copy of academic records.',
                        'work_instruction' => $this->demoFiles['work-instructions/transcript-request.pdf']['path'],
                        'sla_days' => 7,
                        'fields' => [
                            ['Purpose', FieldType::Select, true, ['Employment', 'Board examination', 'Graduate school', 'Scholarship']],
                            ['Year Graduated', FieldType::Text, false],
                            ['Authorization Letter', FieldType::File, false],
                        ],
                    ],
                    'enrollment-certification' => [
                        'name' => 'Certificate of Enrollment',
                        'description' => 'Request certification of current enrollment status.',
                        'work_instruction' => null,
                        'sla_days' => 3,
                        'fields' => [
                            ['Semester', FieldType::Select, true, ['First Semester', 'Second Semester', 'Midyear']],
                            ['Purpose', FieldType::Textarea, true],
                        ],
                    ],
                ],
            ],
            'finance' => [
                'category' => ['slug' => 'demo-payments-and-certifications', 'name' => 'Payments and Certifications', 'description' => 'Billing, payment validation, and financial certificates.'],
                'services' => [
                    'payment-certification' => [
                        'name' => 'Payment Certification',
                        'description' => 'Request validation or certification of payment records.',
                        'work_instruction' => $this->demoFiles['work-instructions/payment-certification.pdf']['path'],
                        'sla_days' => 5,
                        'fields' => [
                            ['Official Receipt Number', FieldType::Text, true],
                            ['Payment Date', FieldType::Date, true],
                            ['Proof of Payment', FieldType::File, true],
                        ],
                    ],
                ],
            ],
            'physical-plant' => [
                'category' => ['slug' => 'demo-facilities-support', 'name' => 'Facilities Support', 'description' => 'Repairs, facilities concerns, and room maintenance.'],
                'services' => [
                    'maintenance-request' => [
                        'name' => 'Campus Maintenance Request',
                        'description' => 'Report classroom, laboratory, office, or campus facility issues.',
                        'work_instruction' => $this->demoFiles['work-instructions/maintenance-request.pdf']['path'],
                        'sla_days' => 4,
                        'fields' => [
                            ['Building and Room', FieldType::Text, true],
                            ['Concern Type', FieldType::Select, true, ['Electrical', 'Plumbing', 'Air-conditioning', 'Furniture', 'Other']],
                            ['Photo of Concern', FieldType::File, false],
                        ],
                    ],
                ],
            ],
        ];

        $services = [];

        foreach ($catalog as $officeKey => $officeCatalog) {
            $categoryData = $officeCatalog['category'];
            $category = ServiceCategory::updateOrCreate(
                ['slug' => $categoryData['slug']],
                [
                    'office_id' => $offices[$officeKey]->id,
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'is_active' => true,
                    'sort_order' => 10,
                ],
            );

            foreach ($officeCatalog['services'] as $slug => $serviceData) {
                $service = ServiceType::updateOrCreate(
                    ['slug' => "demo-{$slug}"],
                    [
                        'service_category_id' => $category->id,
                        'name' => $serviceData['name'],
                        'description' => $serviceData['description'],
                        'work_instruction' => $serviceData['work_instruction'],
                        'sla_days' => $serviceData['sla_days'],
                        'is_active' => true,
                        'sort_order' => 10,
                    ],
                );

                ServiceTypeField::where('service_type_id', $service->id)->delete();

                foreach ($serviceData['fields'] as $index => $field) {
                    ServiceTypeField::create([
                        'service_type_id' => $service->id,
                        'label' => $field[0],
                        'field_type' => $field[1],
                        'is_required' => $field[2],
                        'options' => $field[3] ?? null,
                        'sort_order' => $index + 1,
                    ]);
                }

                $services[$slug] = $service;
            }
        }

        return $services;
    }

    private function seedCannedResponses(array $offices, array $users): void
    {
        $responses = [
            ['office' => null, 'title' => 'Acknowledgement', 'body' => 'Thank you for contacting us. We have received your request and will review the details shortly.'],
            ['office' => 'icto', 'title' => 'Request screenshot', 'body' => 'Please upload a screenshot of the error message so we can verify the issue.'],
            ['office' => 'registrar', 'title' => 'Document ready notice', 'body' => 'Your requested document is ready for releasing. Please bring a valid ID when claiming it.'],
            ['office' => 'finance', 'title' => 'Payment verification', 'body' => 'We are validating your payment record. Please ensure the official receipt number is visible in your attachment.'],
            ['office' => 'physical-plant', 'title' => 'Maintenance scheduled', 'body' => 'A facilities staff member has been scheduled to inspect the reported concern.'],
        ];

        foreach ($responses as $response) {
            CannedResponse::updateOrCreate(
                [
                    'office_id' => $response['office'] ? $offices[$response['office']]->id : null,
                    'title' => $response['title'],
                ],
                [
                    'body' => $response['body'],
                    'created_by' => $users['super']->id,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedTickets(array $offices, array $users, array $services): void
    {
        $this->seedTicket(
            'DEMO-ITO-26-0001',
            $users['ana'],
            $offices['icto'],
            $services['bu-email-account'],
            $users['icto_staff'],
            TicketStatus::Pending,
            TicketPriority::Normal,
            'Create BU email account for enrollment portal',
            'I need a BU email account so I can access the enrollment portal and official Google Classroom spaces.',
            ['Student ID Number' => '2026-00041', 'Account Concern' => 'New account'],
            [
                ['actor' => 'ana', 'event' => EventType::Created, 'to' => TicketStatus::Pending, 'note' => 'Student submitted a new email account request.'],
            ],
            [
                ['sender' => 'ana', 'body' => 'Good morning. I attached my student ID and registration form for verification.', 'at' => '-2 days'],
                ['sender' => 'icto_staff', 'body' => 'Thanks, Ana. We are checking your student record before creating the account.', 'at' => '-1 day'],
            ],
            [
                ['file' => 'attachments/student-id-front.jpg', 'name' => 'student-id-front.jpg', 'mime' => 'image/jpeg', 'uploader' => 'ana'],
                ['file' => 'attachments/registration-form.pdf', 'name' => 'registration-form.pdf', 'mime' => 'application/pdf', 'uploader' => 'ana'],
            ],
        );

        $this->seedTicket(
            'DEMO-REG-26-0002',
            $users['juan'],
            $offices['registrar'],
            $services['transcript-request'],
            $users['registrar_staff'],
            TicketStatus::InProgress,
            TicketPriority::High,
            'Transcript of Records for scholarship renewal',
            'Requesting an official transcript for scholarship renewal. Deadline is next week.',
            ['Purpose' => 'Scholarship', 'Year Graduated' => 'N/A'],
            [
                ['actor' => 'juan', 'event' => EventType::Created, 'to' => TicketStatus::Pending, 'note' => 'Transcript request submitted.'],
                ['actor' => 'registrar_admin', 'event' => EventType::Assigned, 'from' => TicketStatus::Pending, 'to' => TicketStatus::Assigned, 'note' => 'Assigned to records staff.'],
                ['actor' => 'registrar_staff', 'event' => EventType::StatusChanged, 'from' => TicketStatus::Assigned, 'to' => TicketStatus::InProgress, 'note' => 'Records verification started.'],
            ],
            [
                ['sender' => 'juan', 'body' => 'I need this document for scholarship renewal. Thank you.', 'at' => '-5 days'],
                ['sender' => 'registrar_staff', 'body' => 'We have started checking your records. We will update you once ready for assessment.', 'at' => '-4 days'],
                ['sender' => 'registrar_staff', 'body' => 'Internal note: Validate grades for second semester before releasing.', 'internal' => true, 'at' => '-3 days'],
            ],
        );

        $forwarded = $this->seedTicket(
            'DEMO-REG-26-0003',
            $users['maria'],
            $offices['finance'],
            $services['payment-certification'],
            $users['finance_staff'],
            TicketStatus::Forwarded,
            TicketPriority::Normal,
            'Payment certification for graduation clearance',
            'Requesting certification that my graduation fee and clearance payments are posted.',
            ['Official Receipt Number' => 'OR-2026-44819', 'Payment Date' => now()->subDays(9)->toDateString()],
            [
                ['actor' => 'maria', 'event' => EventType::Created, 'to' => TicketStatus::Pending, 'note' => 'Payment certification request submitted.'],
                ['actor' => 'registrar_staff', 'event' => EventType::Forwarded, 'from' => TicketStatus::Pending, 'to' => TicketStatus::Forwarded, 'note' => 'Forwarded to Finance Office for payment validation.'],
            ],
            [
                ['sender' => 'maria', 'body' => 'I uploaded my receipt for validation.', 'at' => '-6 days'],
                ['sender' => 'finance_staff', 'body' => 'We are validating the payment in the cashier records.', 'at' => '-5 days'],
            ],
            [
                ['file' => 'attachments/proof-of-payment.pdf', 'name' => 'proof-of-payment.pdf', 'mime' => 'application/pdf', 'uploader' => 'maria'],
            ],
        );

        ForwardingLog::updateOrCreate(
            ['ticket_id' => $forwarded->id, 'from_office_id' => $offices['registrar']->id, 'to_office_id' => $offices['finance']->id],
            [
                'forwarded_by' => $users['registrar_staff']->id,
                'accepted_by' => $users['finance_staff']->id,
                'credit_type' => CreditType::AcceptCredit,
                'note' => 'Registrar needs Finance validation before releasing clearance.',
                'forwarded_at' => now()->subDays(6),
                'responded_at' => now()->subDays(5),
            ],
        );

        $hold = $this->seedTicket(
            'DEMO-PPO-26-0004',
            $users['carlo'],
            $offices['physical-plant'],
            $services['maintenance-request'],
            $users['ppo_staff'],
            TicketStatus::OnHold,
            TicketPriority::Urgent,
            'Air-conditioning leak in computer laboratory',
            'There is a visible leak near the front workstations in Computer Laboratory 2.',
            ['Building and Room' => 'CS Building - Computer Laboratory 2', 'Concern Type' => 'Air-conditioning'],
            [
                ['actor' => 'carlo', 'event' => EventType::Created, 'to' => TicketStatus::Pending, 'note' => 'Maintenance request submitted.'],
                ['actor' => 'ppo_staff', 'event' => EventType::StatusChanged, 'from' => TicketStatus::Pending, 'to' => TicketStatus::OnHold, 'note' => 'Awaiting clearer photo and schedule confirmation.'],
            ],
            [
                ['sender' => 'carlo', 'body' => 'The leak started this morning and may reach the electrical outlets.', 'at' => '-1 day'],
                ['sender' => 'ppo_staff', 'body' => 'Please upload a clearer photo showing the affected area and nearby equipment.', 'request_attachment' => true, 'at' => '-20 hours'],
                ['guest' => 'Carlo Garcia', 'body' => 'I uploaded another photo through the public tracker.', 'at' => '-18 hours'],
            ],
            [
                ['file' => 'attachments/lab-photo.jpg', 'name' => 'lab-photo.jpg', 'mime' => 'image/jpeg', 'uploader' => 'carlo'],
            ],
        );

        $requestingMessage = TicketMessage::where('ticket_id', $hold->id)
            ->where('requests_attachment', true)
            ->first();

        if ($requestingMessage) {
            $this->attachDemoFile($hold, 'attachments/clearance-slip.pdf', 'clearer-lab-photo.pdf', 'application/pdf', null, $requestingMessage);
        }

        $this->seedTicket(
            'DEMO-ITO-26-0005',
            $users['juan'],
            $offices['icto'],
            $services['bu-email-account'],
            $users['icto_staff'],
            TicketStatus::Resolved,
            TicketPriority::Low,
            'Password reset for university email',
            'I forgot the password to my BU email and need it reset before class.',
            ['Student ID Number' => '2026-00102', 'Account Concern' => 'Password reset'],
            [
                ['actor' => 'juan', 'event' => EventType::Created, 'to' => TicketStatus::Pending, 'note' => 'Password reset request submitted.'],
                ['actor' => 'icto_staff', 'event' => EventType::Resolved, 'from' => TicketStatus::InProgress, 'to' => TicketStatus::Resolved, 'note' => 'Temporary credentials sent to registered recovery email.'],
            ],
            [
                ['sender' => 'juan', 'body' => 'I can no longer access my BU email.', 'at' => '-10 days'],
                ['sender' => 'icto_staff', 'body' => 'Your password has been reset. Please sign in and change it immediately.', 'at' => '-9 days'],
            ],
            [],
            now()->subDays(9),
        );

        $this->seedTicket(
            'DEMO-REG-26-0006',
            $users['ana'],
            $offices['registrar'],
            $services['enrollment-certification'],
            $users['registrar_staff'],
            TicketStatus::Closed,
            TicketPriority::Normal,
            'Certificate of enrollment for internship',
            'Requesting a certificate of enrollment for internship application requirements.',
            ['Semester' => 'Second Semester', 'Purpose' => 'Internship application'],
            [
                ['actor' => 'ana', 'event' => EventType::Created, 'to' => TicketStatus::Pending, 'note' => 'Certificate request submitted.'],
                ['actor' => 'registrar_staff', 'event' => EventType::Resolved, 'from' => TicketStatus::InProgress, 'to' => TicketStatus::Resolved, 'note' => 'Certificate prepared for release.'],
                ['actor' => 'registrar_admin', 'event' => EventType::Closed, 'from' => TicketStatus::Resolved, 'to' => TicketStatus::Closed, 'note' => 'Requester claimed the document.'],
            ],
            [
                ['sender' => 'registrar_staff', 'body' => 'Your certificate is ready for claiming at Window 2.', 'at' => '-14 days'],
                ['sender' => 'ana', 'body' => 'Claimed today. Thank you.', 'at' => '-13 days'],
            ],
            [],
            now()->subDays(14),
            now()->subDays(13),
        );
    }

    private function seedTicket(
        string $ulid,
        User $requester,
        Office $office,
        ServiceType $service,
        ?User $assignee,
        TicketStatus $status,
        TicketPriority $priority,
        string $subject,
        string $description,
        array $customFields,
        array $history,
        array $messages,
        array $attachments = [],
        mixed $resolvedAt = null,
        mixed $closedAt = null,
    ): Ticket {
        $ticket = Ticket::withTrashed()->updateOrCreate(
            ['ulid' => $ulid],
            [
                'requester_id' => $requester->id,
                'office_id' => $office->id,
                'service_type_id' => $service->id,
                'assigned_to' => $assignee?->id,
                'status' => $status,
                'priority' => $priority,
                'subject' => $subject,
                'description' => $description,
                'custom_fields' => $customFields,
                'resolved_at' => $resolvedAt,
                'closed_at' => $closedAt,
                'deleted_at' => null,
            ],
        );

        TicketHistory::where('ticket_id', $ticket->id)->delete();
        TicketAttachment::where('ticket_id', $ticket->id)->delete();
        TicketMessage::where('ticket_id', $ticket->id)->delete();

        foreach ($history as $index => $event) {
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id' => $this->resolveActor($event['actor'])->id,
                'event_type' => $event['event'],
                'from_status' => $event['from'] ?? null,
                'to_status' => $event['to'] ?? null,
                'note' => $event['note'] ?? null,
                'meta' => ['demo' => true, 'sequence' => $index + 1],
                'created_at' => now()->subDays(12)->addHours($index * 8),
                'updated_at' => now()->subDays(12)->addHours($index * 8),
            ]);
        }

        foreach ($messages as $message) {
            TicketMessage::create([
                'ticket_id' => $ticket->id,
                'sender_id' => isset($message['sender']) ? $this->resolveActor($message['sender'])->id : null,
                'guest_name' => $message['guest'] ?? null,
                'body' => $message['body'],
                'is_internal_note' => $message['internal'] ?? false,
                'is_canned_response' => false,
                'requests_attachment' => $message['request_attachment'] ?? false,
                'seen_at' => ($message['sender'] ?? null) === $requester->email ? now() : null,
                'created_at' => now()->modify($message['at'] ?? '-1 day'),
                'updated_at' => now()->modify($message['at'] ?? '-1 day'),
            ]);
        }

        foreach ($attachments as $attachment) {
            $this->attachDemoFile(
                $ticket,
                $attachment['file'],
                $attachment['name'],
                $attachment['mime'],
                $this->resolveActor($attachment['uploader']),
            );
        }

        return $ticket;
    }

    private function attachDemoFile(Ticket $ticket, string $fileKey, string $originalName, string $mime, ?User $uploader, ?TicketMessage $message = null): void
    {
        $file = $this->demoFiles[$fileKey];

        TicketAttachment::create([
            'ticket_id' => $ticket->id,
            'ticket_message_id' => $message?->id,
            'uploader_id' => $uploader?->id,
            'disk' => 'local',
            'path' => $file['path'],
            'original_filename' => $originalName,
            'mime_type' => $mime,
            'size_bytes' => $file['size'],
            'compressed_size_bytes' => (int) ($file['size'] * 0.65),
        ]);
    }

    private function resolveActor(string $key): User
    {
        $map = [
            'ana' => 'ana.reyes@student.bicol-u.edu.ph',
            'juan' => 'juan.santos@student.bicol-u.edu.ph',
            'maria' => 'maria.delacruz@student.bicol-u.edu.ph',
            'carlo' => 'carlo.garcia@student.bicol-u.edu.ph',
            'icto_staff' => 'icto.staff@bicol-u.edu.ph',
            'registrar_admin' => 'registrar.admin@bicol-u.edu.ph',
            'registrar_staff' => 'registrar.staff@bicol-u.edu.ph',
            'finance_staff' => 'finance.staff@bicol-u.edu.ph',
            'ppo_staff' => 'ppo.staff@bicol-u.edu.ph',
            'super' => 'demo.super@bicol-u.edu.ph',
        ];

        return User::where('email', $map[$key] ?? $key)->firstOrFail();
    }
}
