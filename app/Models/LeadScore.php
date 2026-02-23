<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadScore extends Model
{
    protected $fillable = [
        'contact_id',
        'score',
        'engagement_score',
        'demographic_score',
        'behavior_score',
        'last_calculated_at',
    ];

    protected $casts = [
        'score' => 'integer',
        'engagement_score' => 'integer',
        'demographic_score' => 'integer',
        'behavior_score' => 'integer',
        'last_calculated_at' => 'datetime',
    ];

    /**
     * Get the contact that owns the lead score.
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Get score label based on score value.
     */
    public static function getScoreLabel(int $score): string
    {
        if ($score >= 80) return 'Hot';
        if ($score >= 60) return 'Warm';
        if ($score >= 40) return 'Cool';
        return 'Cold';
    }

    /**
     * Get score color based on score value.
     */
    public static function getScoreColor(int $score): string
    {
        if ($score >= 80) return '#22c55e'; // green
        if ($score >= 60) return '#f59e0b'; // amber
        if ($score >= 40) return '#3b82f6'; // blue
        return '#6b7280'; // gray
    }

    /**
     * Calculate lead score for a contact.
     */
    public static function calculateForContact(Contact $contact): self
    {
        $demographicScore = self::calculateDemographicScore($contact);
        $engagementScore = self::calculateEngagementScore($contact);
        $behaviorScore = self::calculateBehaviorScore($contact);

        // Weighted average: 30% demographic, 40% engagement, 30% behavior
        $totalScore = round(
            ($demographicScore * 0.30) + 
            ($engagementScore * 0.40) + 
            ($behaviorScore * 0.30)
        );

        return self::updateOrCreate(
            ['contact_id' => $contact->id],
            [
                'score' => $totalScore,
                'demographic_score' => $demographicScore,
                'engagement_score' => $engagementScore,
                'behavior_score' => $behaviorScore,
                'last_calculated_at' => now(),
            ]
        );
    }

    /**
     * Calculate demographic score based on contact fields.
     */
    private static function calculateDemographicScore(Contact $contact): int
    {
        $score = 0;
        $maxScore = 100;

        // Has name
        if ($contact->name) $score += 20;

        // Has email
        if ($contact->email) $score += 25;

        // Has company
        if ($contact->company) $score += 20;

        // Has address
        if ($contact->address) $score += 15;

        // Has profile picture
        if ($contact->profile_picture) $score += 10;

        // Has tags
        if ($contact->tags && count($contact->tags) > 0) $score += 10;

        return min($score, $maxScore);
    }

    /**
     * Calculate engagement score based on interactions.
     */
    private static function calculateEngagementScore(Contact $contact): int
    {
        $score = 0;
        $maxScore = 100;

        // Recent contact (last 7 days)
        if ($contact->last_contacted_at) {
            $daysSinceContact = $contact->last_contacted_at->diffInDays(now());
            if ($daysSinceContact <= 7) $score += 30;
            elseif ($daysSinceContact <= 30) $score += 15;
            elseif ($daysSinceContact <= 90) $score += 5;
        }

        // Count messages
        $messageCount = $contact->messages()->count();
        if ($messageCount > 50) $score += 30;
        elseif ($messageCount > 20) $score += 20;
        elseif ($messageCount > 10) $score += 15;
        elseif ($messageCount > 0) $score += 10;

        // Has orders
        $orderCount = $contact->orders()->count();
        if ($orderCount > 0) $score += 25;
        
        // Has deals
        $dealCount = $contact->deals()->count();
        if ($dealCount > 0) $score += 15;

        return min($score, $maxScore);
    }

    /**
     * Calculate behavior score based on activities.
     */
    private static function calculateBehaviorScore(Contact $contact): int
    {
        $score = 0;
        $maxScore = 100;

        // Check recent activity
        $recentActivities = $contact->activities()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($recentActivities > 20) $score += 40;
        elseif ($recentActivities > 10) $score += 30;
        elseif ($recentActivities > 5) $score += 20;
        elseif ($recentActivities > 0) $score += 10;

        // Check tickets (support engagement)
        $ticketCount = $contact->tickets()->count();
        if ($ticketCount > 0) $score += 20;

        // Check campaign responses
        $campaignResponses = \App\Models\Campaign::whereJsonContains('recipients', $contact->id)->count();
        if ($campaignResponses > 0) $score += 20;

        // Segment based scoring
        if ($contact->segment_id) {
            $score += 20;
        }

        return min($score, $maxScore);
    }
}
