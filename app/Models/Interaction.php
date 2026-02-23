<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'ticket_id',
        'campaign_id',
        'direction',
        'channel',
        'type',
        'content',
        'media',
        'message_id',
        'status',
        'user_id',
        'is_automated',
        'is_from_bot',
        'sent_at',
        'delivered_at',
        'read_at',
    ];

    protected $casts = [
        'media' => 'array',
        'is_automated' => 'boolean',
        'is_from_bot' => 'boolean',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeByChannel($query, $channel)
    {
        return $query->where('channel', $channel);
    }

    // Methods
    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'sent' => 'blue',
            'delivered' => 'purple',
            'read' => 'green',
            'failed' => 'red',
        ];
        
        return $colors[$this->status] ?? 'gray';
    }
}
