<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chatbot extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
        'flows',
        'keywords',
        'default_response',
        'fallback_response',
        'handover_enabled',
        'handover_to',
        'working_hours',
        'session_id',
        'created_by',
    ];

    protected $casts = [
        'flows' => 'array',
        'keywords' => 'array',
        'default_response' => 'array',
        'fallback_response' => 'array',
        'handover_enabled' => 'boolean',
        'working_hours' => 'array',
    ];

    // Relationships
    public function handoverAgent()
    {
        return $this->belongsTo(User::class, 'handover_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sessions()
    {
        return $this->hasMany(ChatbotSession::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // Methods
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isWithinWorkingHours()
    {
        if (empty($this->working_hours)) {
            return true;
        }

        $now = now();
        $dayOfWeek = $now->dayOfWeek;
        $currentTime = $now->format('H:i');

        $daySchedule = $this->working_hours[$dayOfWeek] ?? null;

        if (!$daySchedule || !isset($daySchedule['start']) || !isset($daySchedule['end'])) {
            return false;
        }

        return $currentTime >= $daySchedule['start'] && $currentTime <= $daySchedule['end'];
    }

    public function findMatchingKeyword($message)
    {
        if (empty($this->keywords)) {
            return null;
        }

        $message = strtolower($message);

        foreach ($this->keywords as $keyword) {
            if (str_contains($message, strtolower($keyword['keyword']))) {
                return $keyword;
            }
        }

        return null;
    }
}
