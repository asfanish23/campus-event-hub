<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductMedia extends Model
{
    use HasFactory;

    protected $table = 'product_media';

    protected $fillable = [
        'product_id',
        'file_path',
        'file_type',
        'order'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        $value = trim($this->file_path);
        if ($value === '') {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

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
