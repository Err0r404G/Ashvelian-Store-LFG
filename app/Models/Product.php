<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id',
        'created_by',
        'name',
        'slug',
        'sku',
        'description',
        'price',
        'cost',
        'compare_at_price',
        'stock_quantity',
        'low_stock_threshold',
        'primary_image_url',
        'images',
        'sizes',
        'colors',
        'specifications',
        'features',
        'rating_average',
        'rating_count',
        'is_featured',
        'featured_sort_order',
        'is_on_sale',
        'sale_sort_order',
        'status',
        'has_size_guide',
        'size_guide_image',
        'variant_stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'images' => 'array',
            'sizes' => 'array',
            'colors' => 'array',
            'specifications' => 'array',
            'features' => 'array',
            'is_featured' => 'boolean',
            'featured_sort_order' => 'integer',
            'is_on_sale' => 'boolean',
            'sale_sort_order' => 'integer',
            'has_size_guide' => 'boolean',
            'variant_stock' => 'array',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function returnRequests()
    {
        return $this->hasMany(ReturnRequest::class);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }
}
