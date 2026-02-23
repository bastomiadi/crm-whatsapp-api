<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Automation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'trigger_type',
        'trigger_config',
        'conditions',
        'actions',
        'is_active',
        'execution_count',
        'last_executed_at',
        'created_by',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
        'last_executed_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(AutomationLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTrigger($query, $triggerType)
    {
        return $query->where('trigger_type', $triggerType);
    }

    // Trigger types
    public static function getTriggerTypes()
    {
        return [
            'contact_created' => 'Contact Created',
            'contact_tagged' => 'Contact Tagged',
            'order_created' => 'Order Created',
            'order_status_changed' => 'Order Status Changed',
            'ticket_created' => 'Ticket Created',
            'ticket_status_changed' => 'Ticket Status Changed',
            'message_received' => 'Message Received',
            'keyword_detected' => 'Keyword Detected',
            'scheduled' => 'Scheduled',
            'webhook' => 'Webhook',
        ];
    }

    // Action types
    public static function getActionTypes()
    {
        return [
            'send_message' => 'Send Message',
            'send_template' => 'Send Template Message',
            'add_tag' => 'Add Tag',
            'remove_tag' => 'Remove Tag',
            'update_contact' => 'Update Contact',
            'create_ticket' => 'Create Ticket',
            'assign_ticket' => 'Assign Ticket',
            'send_notification' => 'Send Notification',
            'trigger_webhook' => 'Trigger Webhook',
            'add_to_segment' => 'Add to Segment',
            'remove_from_segment' => 'Remove from Segment',
        ];
    }
}
