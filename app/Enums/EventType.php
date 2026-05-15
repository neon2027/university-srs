<?php

namespace App\Enums;

enum EventType: string
{
    case Created = 'created';
    case StatusChanged = 'status_changed';
    case Assigned = 'assigned';
    case Forwarded = 'forwarded';
    case NoteAdded = 'note_added';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
