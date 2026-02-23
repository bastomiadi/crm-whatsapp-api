<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MessageTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'category',
        'content',
        'media_url',
        'variables',
        'buttons',
        'is_approved',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'variables' => 'array',
        'buttons' => 'array',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function campaigns()
    {
        return $this->hasMany(Campaign::class);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Methods
    public function parseVariables(array $data)
    {
        $content = $this->content;
        
        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        
        return $content;
    }

    public function getVariablesListAttribute()
    {
        preg_match_all('/\{\{(\w+)\}\}/', $this->content, $matches);
        return $matches[1] ?? [];
    }
}
