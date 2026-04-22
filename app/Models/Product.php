<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function media()
    {
        return $this->hasMany(ProductMedia::class)->orderBy('order');
    }}