<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'nom',
        'prix',
        'old_price',
        'sku',
        'categorie_id',
        'tag_id',
        'color',
        'image_initiale',
        'description'
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(Category::class,'categorie_id');
    }

    /**
     * Get the tag that owns the product.
     */
    public function tag()
    {
        return $this->belongsTo(Tag::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }
}
