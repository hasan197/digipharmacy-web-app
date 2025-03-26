<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'customer_name',
        'customer_phone',
        'total',
        'discount',
        'additional_fee',
        'grand_total',
        'payment_method',
        'payment_details',
        'status',
        'notes'
    ];
    
    protected $casts = [
        'payment_details' => 'array',
    ];

    protected static $autoInvoiceNumber = true;

    public static function withoutInvoiceNumber(callable $callback)
    {
        static::$autoInvoiceNumber = false;
        try {
            return $callback();
        } finally {
            static::$autoInvoiceNumber = true;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!static::$autoInvoiceNumber || $model->invoice_number) {
                return;
            }

            // Format: INV/YYYYMMDD/XXXX
            $today = now();
            $latestInvoice = static::whereDate('created_at', $today)
                ->latest()
                ->first();

            $sequence = $latestInvoice ? (int)substr($latestInvoice->invoice_number, -4) + 1 : 1;
            $model->invoice_number = sprintf(
                'INV/%s/%04d',
                $today->format('Ymd'),
                $sequence
            );
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(SalesOrderDetail::class);
    }
}
