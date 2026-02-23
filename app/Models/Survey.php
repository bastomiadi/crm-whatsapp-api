<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type', // nps, satisfaction, feedback
        'status', // draft, active, closed
        'questions',
        'send_to_all_contacts',
        'contact_segments',
        'send_via_whatsapp',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'questions' => 'array',
        'send_to_all_contacts' => 'boolean',
        'contact_segments' => 'array',
        'send_via_whatsapp' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    const TYPE_NPS = 'nps';
    const TYPE_SATISFACTION = 'satisfaction';
    const TYPE_FEEDBACK = 'feedback';

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';

    /**
     * Get survey responses.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Get available survey types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_NPS => 'NPS (Net Promoter Score)',
            self::TYPE_SATISFACTION => 'Customer Satisfaction',
            self::TYPE_FEEDBACK => 'General Feedback',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    /**
     * Calculate NPS score.
     */
    public function getNpsScoreAttribute(): ?int
    {
        $responses = $this->responses()->whereNotNull('nps_score')->get();
        if ($responses->isEmpty()) return null;

        $promoters = $responses->where('nps_score', '>=', 9)->count();
        $detractors = $responses->where('nps_score', '<=', 6)->count();
        $total = $responses->count();

        if ($total === 0) return null;

        return round((($promoters - $detractors) / $total) * 100);
    }

    /**
     * Calculate average satisfaction score.
     */
    public function getAvgSatisfactionAttribute(): ?float
    {
        $responses = $this->responses()->whereNotNull('satisfaction_score')->get();
        if ($responses->isEmpty()) return null;

        return round($responses->avg('satisfaction_score'), 1);
    }

    /**
     * Get response count.
     */
    public function getResponseCountAttribute(): int
    {
        return $this->responses()->count();
    }

    /**
     * Get response rate.
     */
    public function getResponseRateAttribute(): float
    {
        // This would need to track sent surveys
        return 0;
    }
}
