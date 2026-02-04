<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'reviewer_name',
        'rating',
        'review_text',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
