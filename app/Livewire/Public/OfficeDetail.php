<?php

namespace App\Livewire\Public;

use App\Models\Office;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('components.layouts.public')]
class OfficeDetail extends Component
{
    #[Locked]
    public string $slug = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render(): View
    {
        $office = Office::where('slug', $this->slug)
            ->where('is_active', true)
            ->with([
                'serviceCategories' => function ($q) {
                    $q->where('is_active', true)
                        ->orderBy('sort_order')
                        ->with([
                            'serviceTypes' => function ($q2) {
                                $q2->where('is_active', true)->orderBy('sort_order');
                            },
                        ]);
                },
            ])
            ->firstOrFail();

        $canRequest = auth()->check()
            && auth()->user()->hasRole('student')
            && auth()->user()->onboarding_completed_at !== null;

        return view('livewire.public.office-detail', compact('office', 'canRequest'));
    }
}
