<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id',
        'contact_id',
        'nps_score',
        'satisfaction_score',
        'feedback',
        'answers',
        'completed_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the survey that owns the response.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the contact that owns the response.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get NPS label.
     */
    public function getNpsLabelAttribute(): string
    {
        if ($this->nps_score >= 9) return 'Promoter';
        if ($this->nps_score >= 7) return 'Passive';
        return 'Detractor';
    }

    /**
     * Get NPS color.
     */
    public function getNpsColorAttribute(): string
    {
        if ($this->nps_label === 'Promoter') return '#22c55e';
        if ($this->nps_label === 'Passive') return '#f59e0b';
        return '#ef4444';
    }
}
