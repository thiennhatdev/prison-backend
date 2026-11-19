<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['is_active' => 'boolean', 'price' => 'integer'];

    protected $appends = ['category'];

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function getCategoryAttribute(): ?string
    {
        return $this->productCategory?->name;
    }

    public function getImageUrlAttribute(?string $value): ?string
    {
        return $this->image_path ? asset('storage/'.$this->image_path) : $value;
    }
}
