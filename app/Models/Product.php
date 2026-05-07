<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'category',
        'description',
        'image',
        'club_id'
    ];

    // Always eager load media relationship
    protected $with = ['media'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class)->orderBy('order');
    }

    public function getPrimaryImagePathAttribute(): ?string
    {
        $primaryPhoto = $this->media->firstWhere('file_type', 'photo');

        if ($primaryPhoto && !empty($primaryPhoto->file_path)) {
            return $primaryPhoto->file_path;
        }

        return $this->image ?: null;
    }

    public function getPrimaryImageUrlAttribute(): ?string
    {
        return self::normalizeImageUrl($this->primary_image_path);
    }

    public static function normalizeImageUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $value = trim($path);
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        // Ignore Windows-style absolute/local filesystem paths in DB values.
        if (preg_match('/^[A-Za-z]:\\\\|^\\\\\\\\/', $value)) {
            return null;
        }

        $value = str_replace('\\\\', '/', $value);
        $value = ltrim($value, '/');

        if (Str::startsWith($value, 'storage/')) {
            $value = substr($value, strlen('storage/'));
        }

        return asset('storage/' . $value);
    }
}