<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'priority',
        'status',
        'contact_id',
        'assigned_to',
        'created_by',
        'reminder_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Priority constants
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', now()->toDateString())
            ->whereNotIn('status', [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function scopeMyTasks($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->due_date && $this->due_date->isPast() 
            && !in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            self::STATUS_PENDING => 'yellow',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'gray',
        ];
        return $colors[$this->status] ?? 'gray';
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_MEDIUM => 'yellow',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_URGENT => 'red',
        ];
        return $colors[$this->priority] ?? 'gray';
    }
}
