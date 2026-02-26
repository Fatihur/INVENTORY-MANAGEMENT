<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inventory Tax Settings
    |--------------------------------------------------------------------------
    |
    | Default tax rate for purchase orders and sales orders.
    | This value is used when calculating tax amounts.
    |
    */
    'tax_rate' => env('INVENTORY_TAX_RATE', 0.11), // 11% default

    /*
    |--------------------------------------------------------------------------
    | Stock Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for stock management and calculations.
    |
    */
    'stock' => [
        // Number of days to look back for calculating average daily usage
        'adu_lookback_days' => env('INVENTORY_ADU_DAYS', 30),

        // Default lead time in days for new products
        'default_lead_time_days' => env('INVENTORY_DEFAULT_LEAD_TIME', 7),

        // Whether to allow negative stock
        'allow_negative_stock' => env('INVENTORY_ALLOW_NEGATIVE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Order Number Settings
    |--------------------------------------------------------------------------
    |
    | Prefixes for auto-generated order numbers.
    |
    */
    'prefixes' => [
        'purchase_order' => env('INVENTORY_PO_PREFIX', 'PO'),
        'goods_receipt' => env('INVENTORY_GR_PREFIX', 'GR'),
        'sales_order' => env('INVENTORY_SO_PREFIX', 'SO'),
        'stock_opname' => env('INVENTORY_OP_PREFIX', 'OP'),
        'batch' => env('INVENTORY_BATCH_PREFIX', 'BATCH'),
        'customer' => env('INVENTORY_CUST_PREFIX', 'CUST'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Expiry Settings
    |--------------------------------------------------------------------------
    |
    | Settings for handling product expiry dates.
    |
    */
    'expiry' => [
        // Number of days before expiry to show warning
        'warning_days' => env('INVENTORY_EXPIRY_WARNING_DAYS', 7),

        // Number of days before expiry to show "expiring soon" notice
        'expiring_soon_days' => env('INVENTORY_EXPIRING_SOON_DAYS', 30),

        // Whether to reject expired products on receipt
        'reject_expired' => env('INVENTORY_REJECT_EXPIRED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Approval Settings
    |--------------------------------------------------------------------------
    |
    | Settings for approval workflows.
    |
    */
    'approval' => [
        // Minimum amount requiring approval for purchase orders
        'po_approval_threshold' => env('INVENTORY_PO_APPROVAL_THRESHOLD', 1000000),

        // Minimum amount requiring approval for sales orders
        'so_approval_threshold' => env('INVENTORY_SO_APPROVAL_THRESHOLD', 500000),
    ],
];
