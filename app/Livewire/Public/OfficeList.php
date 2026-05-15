<?php

namespace App\Livewire\Public;

use App\Models\Office;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class OfficeList extends Component
{
    public function render(): View
    {
        $offices = Office::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.public.office-list', compact('offices'));
    }
}
