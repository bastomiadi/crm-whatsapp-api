<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ticket extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ticket_number',
        'contact_id',
        'assigned_to',
        'subject',
        'description',
        'status',
        'priority',
        'category',
        'first_response_at',
        'resolved_at',
        'closed_at',
        'response_time',
        'satisfaction_rating',
        'resolution_notes',
    ];

    protected $casts = [
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
        'satisfaction_rating' => 'decimal:2',
    ];

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting_customer']);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // Methods
    public function getStatusColorAttribute()
    {
        $colors = [
            'open' => 'red',
            'in_progress' => 'yellow',
            'waiting_customer' => 'blue',
            'resolved' => 'green',
            'closed' => 'gray',
        ];
        
        return $colors[$this->status] ?? 'gray';
    }

    public function getPriorityColorAttribute()
    {
        $colors = [
            'low' => 'gray',
            'medium' => 'blue',
            'high' => 'yellow',
            'urgent' => 'red',
        ];
        
        return $colors[$this->priority] ?? 'gray';
    }

    public function getIsOverdueAttribute()
    {
        if ($this->status === 'closed' || $this->status === 'resolved') {
            return false;
        }
        
        // Consider overdue if open for more than 24 hours without response
        return $this->created_at->diffInHours(now()) > 24;
    }
    
    public function getJsDescriptionAttribute()
    {
        return str_replace(["'", "\n", "\r"], ["\\'", " ", ""], $this->description ?? '');
    }
}
