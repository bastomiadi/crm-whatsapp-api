<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'automation_id',
        'contact_id',
        'trigger_type',
        'trigger_data',
        'executed_actions',
        'status',
        'error_message',
    ];

    protected $casts = [
        'trigger_data' => 'array',
        'executed_actions' => 'array',
    ];

    // Relationships
    public function automation()
    {
        return $this->belongsTo(Automation::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}
