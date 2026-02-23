<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Segment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'criteria',
        'is_dynamic',
    ];

    protected $casts = [
        'criteria' => 'array',
        'is_dynamic' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($segment) {
            if (empty($segment->slug)) {
                $segment->slug = Str::slug($segment->name);
            }
        });
    }

    // Relationships
    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    // Methods
    public function getContactsCountAttribute()
    {
        return $this->contacts()->count();
    }

    public function getActiveContactsCountAttribute()
    {
        return $this->contacts()->active()->count();
    }
}
