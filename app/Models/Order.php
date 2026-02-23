<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_number',
        'contact_id',
        'status',
        'total_amount',
        'currency',
        'items',
        'shipping_address',
        'shipping_method',
        'tracking_number',
        'ordered_at',
        'confirmed_at',
        'shipped_at',
        'delivered_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'items' => 'array',
        'total_amount' => 'decimal:2',
        'ordered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the user who created this order.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', ['delivered']);
    }

    // Methods
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'processing' => 'indigo',
            'shipped' => 'purple',
            'delivered' => 'green',
            'cancelled' => 'red',
            'refunded' => 'gray',
        ];
        
        return $colors[$this->status] ?? 'gray';
    }

    public function getStatusLabelAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getItemsCountAttribute()
    {
        return count($this->items ?? []);
    }
}
