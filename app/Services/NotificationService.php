<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Batch;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    public function sendLowStockAlert(Product $product)
    {
        $minStock = $product->min_stock ?? 0;
        $currentStock = $product->stocks->sum('qty') ?? 0;

        if ($currentStock <= $minStock) {
            // Log notification to database or send email
            $this->notifyAdmins('Low Stock Alert', "Product {$product->name} is low on stock. Current: {$currentStock}, Min: {$minStock}");
        }
    }

    public function sendExpiryAlert(Batch $batch)
    {
        if ($batch->isExpiringSoon(30)) {
            $this->notifyAdmins('Expiry Alert', "Batch {$batch->batch_number} is expiring soon on {$batch->expiry_date->format('Y-m-d')}");
        }
    }

    public function notifyAdmins(string $subject, string $message)
    {
        // Store notification in database or send email
        // For now, we'll log it - you can integrate with email/SMS later
        \Log::info("NOTIFICATION: {$subject} - {$message}");

        // You can add email sending here:
        // Mail::raw($message, function ($mail) use ($subject) {
        //     $mail->to(config('mail.admin_email'))
        //          ->subject($subject);
        // });
    }

    public function checkAllLowStock()
    {
        $products = Product::with('stocks')->get();

        foreach ($products as $product) {
            $this->sendLowStockAlert($product);
        }
    }

    public function checkExpiringBatches()
    {
        $batches = Batch::where('is_active', true)
            ->whereNotNull('expiry_date')
            ->get();

        foreach ($batches as $batch) {
            $this->sendExpiryAlert($batch);
        }
    }
}
