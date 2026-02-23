<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'phone',
        'name',
        'email',
        'company',
        'address',
        'profile_picture',
        'status',
        'segment_id',
        'tags',
        'custom_fields',
        'notes',
        'last_contacted_at',
        'created_by',
    ];

    protected $casts = [
        'tags' => 'array',
        'custom_fields' => 'array',
        'last_contacted_at' => 'datetime',
    ];

    // Relationships
    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    public function activities()
    {
        return $this->hasMany(Interaction::class);
    }

    public function chatbotSessions()
    {
        return $this->hasMany(ChatbotSession::class);
    }

    public function deals()
    {
        return $this->hasMany(Deal::class);
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function leadScore()
    {
        return $this->hasOne(LeadScore::class);
    }

    /**
     * Get the user who created this contact.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBySegment($query, $segmentId)
    {
        return $query->where('segment_id', $segmentId);
    }

    public function scopeWithTag($query, $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    // Methods
    public function getDisplayNameAttribute()
    {
        return $this->name ?? $this->phone;
    }

    public function getInitialsAttribute()
    {
        if ($this->name) {
            $parts = explode(' ', $this->name);
            return strtoupper(substr($parts[0], 0, 1) . (isset($parts[1]) ? substr($parts[1], 0, 1) : ''));
        }
        return strtoupper(substr($this->phone, 0, 2));
    }

    public function getLastOrderAttribute()
    {
        return $this->orders()->latest()->first();
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()->where('status', '!=', 'cancelled')->sum('total_amount');
    }

    public function getOpenTicketsCountAttribute()
    {
        return $this->tickets()->whereIn('status', ['open', 'in_progress', 'waiting_customer'])->count();
    }
}
