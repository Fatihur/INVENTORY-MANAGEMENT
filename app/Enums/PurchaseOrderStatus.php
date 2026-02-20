<?php

namespace App\Enums;

enum PurchaseOrderStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case APPROVED = 'approved';
    case PARTIAL = 'partial';
    case RECEIVED = 'received';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SENT => 'Sent',
            self::APPROVED => 'Approved',
            self::PARTIAL => 'Partially Received',
            self::RECEIVED => 'Fully Received',
            self::CLOSED => 'Closed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::APPROVED => 'green',
            self::PARTIAL => 'yellow',
            self::RECEIVED => 'emerald',
            self::CLOSED => 'slate',
            self::CANCELLED => 'red',
        };
    }
}
