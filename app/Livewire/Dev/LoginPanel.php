<?php

namespace App\Livewire\Dev;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class LoginPanel extends Component
{
    public ?int $userId = null;

    #[Computed]
    public function users(): Collection
    {
        return User::with('roles')->orderBy('name')->get();
    }

    public function login(): void
    {
        abort_unless(app()->environment(['local', 'testing']), 403);

        if (! $this->userId) {
            return;
        }

        $user = User::findOrFail($this->userId);

        Auth::login($user, remember: true);

        if ($user->hasAnyRole(['super_admin', 'office_admin', 'staff'])) {
            $this->redirect('/admin', navigate: false);

            return;
        }

        if ($user->onboarding_completed_at === null) {
            $this->redirect(route('portal.onboarding'), navigate: false);

            return;
        }

        $this->redirect(route('portal.tickets.index'), navigate: false);
    }

    public function render(): View
    {
        return view('livewire.dev.login-panel');
    }
}
