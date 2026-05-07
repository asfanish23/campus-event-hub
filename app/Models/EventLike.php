<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class EventLike extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'event_id'];
    public $timestamps = false;

    public function getTable()
    {
        static $resolvedTable = null;

        if ($resolvedTable !== null) {
            return $resolvedTable;
        }

        if (Schema::hasTable('liked_events')) {
            return $resolvedTable = 'liked_events';
        }

        if (Schema::hasTable('event_likes')) {
            return $resolvedTable = 'event_likes';
        }

        return $resolvedTable = 'liked_events';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
