<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ChatbotSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_id',
        'contact_id',
        'session_id',
        'current_node',
        'context',
        'history',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'context' => 'array',
        'history' => 'array',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            if (empty($session->session_id)) {
                $session->session_id = Str::uuid()->toString();
            }
        });
    }

    // Relationships
    public function chatbot()
    {
        return $this->belongsTo(Chatbot::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    // Methods
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function addHistory($message, $type = 'user')
    {
        $history = $this->history ?? [];
        $history[] = [
            'type' => $type,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ];
        
        $this->history = $history;
        $this->save();
    }

    public function setContext($key, $value)
    {
        $context = $this->context ?? [];
        $context[$key] = $value;
        $this->context = $context;
        $this->save();
    }

    public function getContext($key, $default = null)
    {
        return $this->context[$key] ?? $default;
    }
}
