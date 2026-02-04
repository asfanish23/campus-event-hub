<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventMedia extends Model
{
    use HasFactory;

    protected $table = 'event_media';

    protected $fillable = [
        'event_id',
        'file_path',
        'file_type',
        'order'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
