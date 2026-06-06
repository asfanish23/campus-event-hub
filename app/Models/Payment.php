<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bill_code',
        'external_reference_no',
        'payment_type',
        'related_id',
        'amount',
        'status',
        'billpl_code',
        'bill_name',
        'bill_description',
        'bill_url',
        'transaction_time',
        'payment_reference',
        'callback_response',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_time' => 'datetime',
        'callback_response' => 'array',
    ];

    /**
     * Relationship: Payment belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the related product if payment_type is merchandise
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'related_id')->where('payment_type', 'merchandise');
    }

    /**
     * Get the related event if payment_type is event_registration
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'related_id')->where('payment_type', 'event_registration');
    }

    /**
     * Scope: Get paid payments only
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope: Get pending payments only
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Check if payment is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(string $reference = null, array $callbackData = null): self
    {
        $this->update([
            'status' => 'paid',
            'transaction_time' => now(),
            'payment_reference' => $reference,
            'callback_response' => $callbackData,
        ]);

        return $this;
    }

    /**
     * Mark payment as failed
     */
    public function markAsFailed(array $callbackData = null): self
    {
        $this->update([
            'status' => 'failed',
            'callback_response' => $callbackData,
        ]);

        return $this;
    }
}
