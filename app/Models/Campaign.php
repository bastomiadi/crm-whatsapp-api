<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Campaign extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'status',
        'template_id',
        'target_segments',
        'target_tags',
        'excluded_contacts',
        'scheduled_at',
        'started_at',
        'completed_at',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'read_count',
        'replied_count',
        'failed_count',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'target_segments' => 'array',
        'target_tags' => 'array',
        'excluded_contacts' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'settings' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($campaign) {
            if (empty($campaign->slug)) {
                $campaign->slug = Str::slug($campaign->name) . '-' . Str::random(6);
            }
        });
    }

    // Relationships
    public function template()
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class);
    }

    // Scopes
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Methods
    public function getProgressPercentageAttribute()
    {
        if ($this->total_recipients === 0) {
            return 0;
        }
        
        return round(($this->sent_count / $this->total_recipients) * 100, 1);
    }

    public function getDeliveryRateAttribute()
    {
        if ($this->sent_count === 0) {
            return 0;
        }
        
        return round(($this->delivered_count / $this->sent_count) * 100, 1);
    }

    public function getReadRateAttribute()
    {
        if ($this->delivered_count === 0) {
            return 0;
        }
        
        return round(($this->read_count / $this->delivered_count) * 100, 1);
    }

    public function getReplyRateAttribute()
    {
        if ($this->delivered_count === 0) {
            return 0;
        }
        
        return round(($this->replied_count / $this->delivered_count) * 100, 1);
    }
}
