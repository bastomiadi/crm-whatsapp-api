<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuickReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'category',
        'attachments',
        'created_by',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
