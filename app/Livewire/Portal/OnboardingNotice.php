<?php

namespace App\Livewire\Portal;

use App\Enums\OnboardingStatus;
use App\Models\Office;
use App\Notifications\EmployeeVerificationRequestedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class OnboardingNotice extends Component
{
    public bool $showOfficeSelector = false;

    public ?int $selectedOfficeId = null;

    #[Computed]
    public function offices()
    {
        return Office::active()->orderBy('name')->get();
    }

    public function continueAsStudent(): void
    {
        auth()->user()->update([
            'onboarding_status' => null,
            'pending_office_id' => null,
        ]);
    }

    public function showReapplyForm(): void
    {
        $this->showOfficeSelector = true;
        $this->selectedOfficeId = null;
    }

    public function reapply(): void
    {
        $this->validate(['selectedOfficeId' => ['required', 'integer', 'exists:offices,id']]);

        $office = Office::findOrFail($this->selectedOfficeId);
        $user = auth()->user();

        $user->update([
            'onboarding_status' => OnboardingStatus::PendingEmployee,
            'pending_office_id' => $office->id,
        ]);

        $admins = $office->staff()->role('office_admin')->get();
        Notification::send(
            $admins,
            new EmployeeVerificationRequestedNotification($user, $office)
        );

        $this->showOfficeSelector = false;
    }

    public function render(): View
    {
        return view('livewire.portal.onboarding-notice');
    }
}
