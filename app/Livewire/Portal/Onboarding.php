<?php

namespace App\Livewire\Portal;

use App\Enums\OnboardingStatus;
use App\Models\Office;
use App\Notifications\EmployeeVerificationRequestedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.portal')]
class Onboarding extends Component
{
    public int $step = 1;

    public ?int $selectedOfficeId = null;

    public function mount(): void
    {
        if (auth()->user()->onboarding_completed_at !== null) {
            $this->redirect(route('portal.tickets.index'), navigate: true);
        }
    }

    #[Computed]
    public function offices()
    {
        return Office::active()->orderBy('name')->get();
    }

    public function chooseStudent(): void
    {
        auth()->user()->update(['onboarding_completed_at' => now()]);
        $this->redirect(route('portal.tickets.index'), navigate: true);
    }

    public function showEmployeePicker(): void
    {
        $this->step = 2;
    }

    public function submitEmployeeRequest(): void
    {
        $this->validate(['selectedOfficeId' => ['required', 'integer', 'exists:offices,id']]);

        $office = Office::findOrFail($this->selectedOfficeId);

        auth()->user()->update([
            'onboarding_status' => OnboardingStatus::PendingEmployee,
            'pending_office_id' => $office->id,
            'onboarding_completed_at' => now(),
        ]);

        $admins = $office->users()->role('office_admin')->get();
        Notification::send(
            $admins,
            new EmployeeVerificationRequestedNotification(auth()->user(), $office)
        );

        $this->redirect(route('portal.tickets.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.portal.onboarding');
    }
}
