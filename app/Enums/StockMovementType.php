<?php

namespace App\Enums;

enum StockMovementType: string
{
    case IN = 'in';
    case OUT = 'out';
    case ADJUST = 'adjust';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';

    public function label(): string
    {
        return match ($this) {
            self::IN => 'Stock In',
            self::OUT => 'Stock Out',
            self::ADJUST => 'Adjustment',
            self::TRANSFER_IN => 'Transfer In',
            self::TRANSFER_OUT => 'Transfer Out',
        };
    }
}
