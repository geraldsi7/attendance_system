<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'section_id',
        'venue_id',
        'starts_at',
        'ends_at',
        'status'
    ];

    public function section(){
        return $this->belongsTo(Section::class);
    }

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function venue(){
        return $this->belongsTo(Venue::class);
    }

    public function attendance(){
        return $this->hasMany(Attendance::class);
    }
}
