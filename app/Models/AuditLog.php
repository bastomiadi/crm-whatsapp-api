<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // Actions
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';
    const ACTION_VIEW = 'view';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_EXPORT = 'export';
    const ACTION_IMPORT = 'import';

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATE => 'Created',
            self::ACTION_UPDATE => 'Updated',
            self::ACTION_DELETE => 'Deleted',
            self::ACTION_VIEW => 'Viewed',
            self::ACTION_LOGIN => 'Logged In',
            self::ACTION_LOGOUT => 'Logged Out',
            self::ACTION_EXPORT => 'Exported',
            self::ACTION_IMPORT => 'Imported',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get action color.
     */
    public function getActionColorAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATE => '#22c55e',
            self::ACTION_UPDATE => '#3b82f6',
            self::ACTION_DELETE => '#ef4444',
            self::ACTION_VIEW => '#6b7280',
            self::ACTION_LOGIN => '#8b5cf6',
            self::ACTION_LOGOUT => '#f59e0b',
            self::ACTION_EXPORT => '#06b6d4',
            self::ACTION_IMPORT => '#f97316',
            default => '#6b7280',
        };
    }

    /**
     * Log an action.
     */
    public static function log(
        string $action,
        string $entityType,
        int $entityId,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null
    ): self {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get available actions.
     */
    public static function getActions(): array
    {
        return [
            self::ACTION_CREATE => 'Created',
            self::ACTION_UPDATE => 'Updated',
            self::ACTION_DELETE => 'Deleted',
            self::ACTION_VIEW => 'Viewed',
            self::ACTION_LOGIN => 'Logged In',
            self::ACTION_LOGOUT => 'Logged Out',
            self::ACTION_EXPORT => 'Exported',
            self::ACTION_IMPORT => 'Imported',
        ];
    }
}
