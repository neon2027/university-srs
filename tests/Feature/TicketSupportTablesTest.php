<?php

use App\Enums\EventType;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketHistory;
use App\Models\TicketMessage;
use App\Models\User;

test('ticket history records a status change event', function () {
    $ticket = Ticket::factory()->create();
    $actor = User::factory()->create();

    TicketHistory::factory()->create([
        'ticket_id' => $ticket->id,
        'actor_id' => $actor->id,
        'event_type' => EventType::StatusChanged,
        'from_status' => 'pending',
        'to_status' => 'assigned',
    ]);

    $history = $ticket->history->first();
    expect($history->event_type)->toBe(EventType::StatusChanged);
    expect($history->to_status)->toBe(TicketStatus::Assigned);
});

test('ticket message can be an internal note', function () {
    $ticket = Ticket::factory()->create();

    TicketMessage::factory()->internalNote()->create(['ticket_id' => $ticket->id]);

    expect($ticket->messages->first()->is_internal_note)->toBeTrue();
});

test('ticket message tracks seen_at timestamp', function () {
    $message = TicketMessage::factory()->create(['seen_at' => null]);

    expect($message->seen_at)->toBeNull();

    $message->update(['seen_at' => now()]);

    expect($message->fresh()->seen_at)->not->toBeNull();
});

test('ticket attachment tracks original and compressed sizes', function () {
    $ticket = Ticket::factory()->create();
    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $ticket->id,
        'size_bytes' => 5_000_000,
        'compressed_size_bytes' => 1_200_000,
    ]);

    expect($attachment->size_bytes)->toBe(5_000_000);
    expect($attachment->compressed_size_bytes)->toBe(1_200_000);
    expect($ticket->attachments)->toHaveCount(1);
});

test('ticket attachment can belong to a message', function () {
    $message = TicketMessage::factory()->create();
    $attachment = TicketAttachment::factory()->create([
        'ticket_id' => $message->ticket_id,
        'ticket_message_id' => $message->id,
    ]);

    expect($attachment->message->id)->toBe($message->id);
});
