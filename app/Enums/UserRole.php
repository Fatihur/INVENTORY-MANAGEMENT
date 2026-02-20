<?php

namespace App\Enums;

enum UserRole: string
{
    case OWNER = 'owner';
    case ADMIN = 'admin';
    case WAREHOUSE = 'warehouse';
    case PURCHASING = 'purchasing';
    case MANAGER = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::ADMIN => 'Administrator',
            self::WAREHOUSE => 'Warehouse Staff',
            self::PURCHASING => 'Purchasing',
            self::MANAGER => 'Manager',
        };
    }
}
