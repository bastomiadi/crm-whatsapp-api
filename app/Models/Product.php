<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'currency',
        'stock',
        'image_url',
        'category',
        'category_id',
        'attributes',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Methods
    public function getFormattedPriceAttribute()
    {
        $currencies = [
            'IDR' => 'Rp',
            'USD' => '$',
            'EUR' => '€',
        ];
        
        $symbol = $currencies[$this->currency] ?? $this->currency;
        
        return $symbol . ' ' . number_format($this->price, 0, ',', '.');
    }

    public function getIsInStockAttribute()
    {
        return $this->stock > 0;
    }
}
