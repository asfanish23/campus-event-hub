<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'attendee_name',
        'matric_no',
        'check_in_time',
        'status',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
