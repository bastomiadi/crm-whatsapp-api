<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'contact_id',
        'assigned_to',
        'stage',
        'value',
        'currency',
        'probability',
        'expected_close_date',
        'actual_close_date',
        'lost_reason',
        'won_note',
        'notes',
        'source',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'integer',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
    ];

    // Deal stages
    const STAGE_LEAD = 'lead';
    const STAGE_QUALIFIED = 'qualified';
    const STAGE_PROPOSAL = 'proposal';
    const STAGE_NEGOTIATION = 'negotiation';
    const STAGE_CLOSED_WON = 'closed_won';
    const STAGE_CLOSED_LOST = 'closed_lost';

    // Deal sources
    const SOURCE_WEBSITE = 'website';
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_SOCIAL_MEDIA = 'social_media';
    const SOURCE_CAMPAIGN = 'campaign';
    const SOURCE_DIRECT = 'direct';
    const SOURCE_OTHER = 'other';

    /**
     * Get the contact that owns the deal.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get the user that owns the deal.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Check if deal is won.
     */
    public function isWon(): bool
    {
        return $this->stage === self::STAGE_CLOSED_WON;
    }

    /**
     * Check if deal is lost.
     */
    public function isLost(): bool
    {
        return $this->stage === self::STAGE_CLOSED_LOST;
    }

    /**
     * Check if deal is active.
     */
    public function isActive(): bool
    {
        return !in_array($this->stage, [self::STAGE_CLOSED_WON, self::STAGE_CLOSED_LOST]);
    }

    /**
     * Get stage color for UI.
     */
    public static function getStageColor(string $stage): string
    {
        return match ($stage) {
            self::STAGE_LEAD => '#6b7280',
            self::STAGE_QUALIFIED => '#3b82f6',
            self::STAGE_PROPOSAL => '#f59e0b',
            self::STAGE_NEGOTIATION => '#8b5cf6',
            self::STAGE_CLOSED_WON => '#22c55e',
            self::STAGE_CLOSED_LOST => '#ef4444',
            default => '#6b7280',
        };
    }

    /**
     * Get available stages.
     */
    public static function getStages(): array
    {
        return [
            self::STAGE_LEAD => 'Lead',
            self::STAGE_QUALIFIED => 'Qualified',
            self::STAGE_PROPOSAL => 'Proposal',
            self::STAGE_NEGOTIATION => 'Negotiation',
            self::STAGE_CLOSED_WON => 'Closed Won',
            self::STAGE_CLOSED_LOST => 'Closed Lost',
        ];
    }

    /**
     * Get available sources.
     */
    public static function getSources(): array
    {
        return [
            self::SOURCE_WEBSITE => 'Website',
            self::SOURCE_REFERRAL => 'Referral',
            self::SOURCE_SOCIAL_MEDIA => 'Social Media',
            self::SOURCE_CAMPAIGN => 'Campaign',
            self::SOURCE_DIRECT => 'Direct',
            self::SOURCE_OTHER => 'Other',
        ];
    }
}
