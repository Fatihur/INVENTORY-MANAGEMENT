<?php

namespace App\Enums;

enum QrCodeType: string
{
    case PRODUCT = 'product';
    case BATCH = 'batch';
    case LOCATION = 'location';

    public function label(): string
    {
        return match ($this) {
            self::PRODUCT => 'Product',
            self::BATCH => 'Batch',
            self::LOCATION => 'Location',
        };
    }
}
