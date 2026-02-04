<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
