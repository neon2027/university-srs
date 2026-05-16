<?php

namespace App\Livewire\Portal;

use App\Enums\EventType;
use App\Enums\FieldType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\ServiceTypeField;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use App\Notifications\TicketSubmittedNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.portal')]
class CreateTicket extends Component
{
    use WithFileUploads;

    public int $step = 1;

    public ?int $officeId = null;

    public ?int $serviceCategoryId = null;

    public ?int $serviceTypeId = null;

    public array $customFields = [];

    public array $fileUploads = [];

    public function mount(?int $prefillServiceTypeId = null): void
    {
        if ($prefillServiceTypeId === null) {
            return;
        }

        $service = ServiceType::with('serviceCategory')
            ->where('is_active', true)
            ->find($prefillServiceTypeId);

        if ($service === null) {
            return;
        }

        $this->serviceTypeId = $service->id;
        $this->serviceCategoryId = $service->service_category_id;
        $this->officeId = $service->serviceCategory->office_id;
        $this->step = 4;
    }

    public function updatedOfficeId(): void
    {
        $this->serviceCategoryId = null;
        $this->serviceTypeId = null;
        $this->customFields = [];
        $this->fileUploads = [];
    }

    public function updatedServiceCategoryId(): void
    {
        $this->serviceTypeId = null;
        $this->customFields = [];
        $this->fileUploads = [];
    }

    public function updatedServiceTypeId(): void
    {
        $this->customFields = [];
        $this->fileUploads = [];
    }

    #[Computed]
    public function offices(): Collection
    {
        return Office::active()->orderBy('sort_order')->orderBy('name')->get();
    }

    #[Computed]
    public function categories(): Collection
    {
        if (! $this->officeId) {
            return new Collection;
        }

        return ServiceCategory::where('office_id', $this->officeId)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function serviceTypes(): Collection
    {
        if (! $this->serviceCategoryId) {
            return new Collection;
        }

        return ServiceType::where('service_category_id', $this->serviceCategoryId)
            ->active()
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function fields(): Collection
    {
        if (! $this->serviceTypeId) {
            return new Collection;
        }

        return ServiceTypeField::where('service_type_id', $this->serviceTypeId)
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function selectedOffice(): ?Office
    {
        return $this->officeId ? Office::find($this->officeId) : null;
    }

    #[Computed]
    public function selectedCategory(): ?ServiceCategory
    {
        return $this->serviceCategoryId ? ServiceCategory::find($this->serviceCategoryId) : null;
    }

    #[Computed]
    public function selectedServiceType(): ?ServiceType
    {
        return $this->serviceTypeId ? ServiceType::find($this->serviceTypeId) : null;
    }

    public function nextStep(): void
    {
        $rules = match ($this->step) {
            1 => ['officeId' => 'required|exists:offices,id'],
            2 => ['serviceCategoryId' => ['required', Rule::exists('service_categories', 'id')->where('office_id', $this->officeId)]],
            3 => ['serviceTypeId' => ['required', Rule::exists('service_types', 'id')->where('service_category_id', $this->serviceCategoryId)]],
            4 => $this->buildFieldRules(),
            default => [],
        };

        if ($rules !== []) {
            $this->validate($rules);
        }

        if ($this->step < 5) {
            $this->step++;
        }
    }

    private function buildFieldRules(): array
    {
        $rules = [];

        foreach ($this->fields as $field) {
            if ($field->field_type === FieldType::File) {
                $rules["fileUploads.{$field->id}"] = $field->is_required
                    ? 'required|file|max:10240|mimes:pdf,jpg,jpeg,png'
                    : 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png';
            } elseif ($field->is_required) {
                $rules["customFields.{$field->id}"] = 'required';
            }
        }

        return $rules;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function submit(): void
    {
        $this->validate([
            'officeId' => 'required|exists:offices,id',
            'serviceCategoryId' => ['required', Rule::exists('service_categories', 'id')->where('office_id', $this->officeId)],
            'serviceTypeId' => ['required', Rule::exists('service_types', 'id')->where('service_category_id', $this->serviceCategoryId)],
            ...$this->buildFieldRules(),
        ]);

        $serviceType = $this->selectedServiceType;

        DB::transaction(function () use ($serviceType): void {
            $ticket = Ticket::create([
                'requester_id' => auth()->id(),
                'office_id' => $this->officeId,
                'service_type_id' => $this->serviceTypeId,
                'status' => TicketStatus::Pending,
                'priority' => TicketPriority::Normal,
                'subject' => $serviceType->name,
                'description' => $this->buildDescription($serviceType),
                'custom_fields' => $this->customFields,
            ]);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'actor_id' => auth()->id(),
                'event_type' => EventType::Created,
            ]);

            foreach ($this->fields as $field) {
                if ($field->field_type === FieldType::File && ! empty($this->fileUploads[$field->id])) {
                    $file = $this->fileUploads[$field->id];
                    $path = $file->store("attachments/{$ticket->ulid}", 'public');

                    TicketAttachment::create([
                        'ticket_id' => $ticket->id,
                        'uploader_id' => auth()->id(),
                        'disk' => 'public',
                        'path' => $path,
                        'original_filename' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size_bytes' => $file->getSize(),
                    ]);
                }
            }

            auth()->user()->notify(new TicketSubmittedNotification($ticket));

            $this->redirect(route('portal.tickets.show', $ticket->ulid), navigate: true);
        });
    }

    private function buildDescription(ServiceType $serviceType): string
    {
        $lines = [
            $serviceType->description ?: "{$serviceType->name} request submitted through the student portal.",
        ];

        foreach ($this->fields as $field) {
            $value = $this->customFields[$field->id] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $lines[] = $field->label.': '.(is_array($value) ? implode(', ', $value) : $value);
        }

        return implode("\n\n", $lines);
    }

    public function render(): View
    {
        return view('livewire.portal.create-ticket');
    }
}
