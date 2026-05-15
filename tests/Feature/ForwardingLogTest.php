<?php

use App\Enums\CreditType;
use App\Models\CannedResponse;
use App\Models\ForwardingLog;
use App\Models\Office;
use App\Models\Ticket;
use App\Models\User;

test('forwarding log is created with null credit type pending acceptance', function () {
    $log = ForwardingLog::factory()->create(['credit_type' => null]);

    expect($log->credit_type)->toBeNull();
    expect($log->responded_at)->toBeNull();
});

test('forwarding log can accept credit', function () {
    $acceptor = User::factory()->create();
    $log = ForwardingLog::factory()->create(['credit_type' => null]);

    $log->update([
        'credit_type' => CreditType::AcceptCredit,
        'accepted_by' => $acceptor->id,
        'responded_at' => now(),
    ]);

    expect($log->fresh()->credit_type)->toBe(CreditType::AcceptCredit);
});

test('forwarding log can be reference only', function () {
    $log = ForwardingLog::factory()->create([
        'credit_type' => CreditType::ReferenceOnly,
        'responded_at' => now(),
    ]);

    expect($log->credit_type)->toBe(CreditType::ReferenceOnly);
});

test('forwarding log belongs to from and to offices', function () {
    $from = Office::factory()->create();
    $to = Office::factory()->create();
    $ticket = Ticket::factory()->create(['office_id' => $from->id]);

    $log = ForwardingLog::factory()->create([
        'ticket_id' => $ticket->id,
        'from_office_id' => $from->id,
        'to_office_id' => $to->id,
    ]);

    expect($log->fromOffice->id)->toBe($from->id);
    expect($log->toOffice->id)->toBe($to->id);
});

test('office-scoped canned response is separate from system-wide', function () {
    $office = Office::factory()->create();
    $staff = User::factory()->create();

    CannedResponse::factory()->create(['office_id' => $office->id, 'created_by' => $staff->id]);
    CannedResponse::factory()->create(['office_id' => null, 'created_by' => $staff->id]);

    expect(CannedResponse::where('office_id', $office->id)->count())->toBe(1);
    expect(CannedResponse::whereNull('office_id')->count())->toBe(1);
});

test('scopeForOffice returns office-specific and system-wide responses', function () {
    $office = Office::factory()->create();
    $other = Office::factory()->create();
    $staff = User::factory()->create();

    $mine = CannedResponse::factory()->create(['office_id' => $office->id, 'created_by' => $staff->id]);
    $global = CannedResponse::factory()->create(['office_id' => null, 'created_by' => $staff->id]);
    $theirs = CannedResponse::factory()->create(['office_id' => $other->id, 'created_by' => $staff->id]);

    $results = CannedResponse::forOffice($office->id)->pluck('id');

    expect($results)->toContain($mine->id)
        ->toContain($global->id)
        ->not->toContain($theirs->id);
});
