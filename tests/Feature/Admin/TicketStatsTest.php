<?php

use App\Enums\TicketStatus;
use App\Filament\Widgets\TicketStatsOverview;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $user = User::factory()->create();
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

test('stats widget shows pending ticket count', function () {
    Ticket::factory()->count(3)->create(['status' => TicketStatus::Pending]);
    Ticket::factory()->count(2)->create(['status' => TicketStatus::Resolved]);

    Livewire::test(TicketStatsOverview::class)
        ->assertSeeText('3');
});

test('stats widget shows resolved today count', function () {
    // Resolved today (should be counted)
    Ticket::factory()->count(2)->resolved()->create();

    // Resolved yesterday (should NOT be counted)
    Ticket::factory()->count(1)->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => now()->subDay(),
    ]);

    Livewire::test(TicketStatsOverview::class)
        ->assertSeeText('2');
});
