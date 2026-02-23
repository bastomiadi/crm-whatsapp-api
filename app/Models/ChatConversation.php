<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'name',
        'session_id',
        'contact_id',
        'assigned_to',
        'status',
        'last_message_at',
        'unread_count',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'unread_count' => 'integer',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    public function markAsRead()
    {
        $this->update(['unread_count' => 0]);
        $this->messages()->where('direction', 'inbound')->whereNull('read_at')->update(['read_at' => now()]);
    }

    public function addMessage($message, $direction, $metadata = [])
    {
        $message = $this->messages()->create([
            'message' => $message,
            'direction' => $direction,
            'metadata' => $metadata,
        ]);

        $this->update([
            'last_message_at' => now(),
            'unread_count' => $direction === 'inbound' ? $this->unread_count + 1 : $this->unread_count,
        ]);

        return $message;
    }
}
