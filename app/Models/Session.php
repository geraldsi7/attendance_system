<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Session extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'lecturer_id',
        'course_id',
        'classe_id',
        'venue_id',
        'starts_at',
        'ends_at',
        'status'
    ];

    public function course(){
        return $this->belongsTo(Course::class);
    }

    public function classe(){
        return $this->belongsTo(Classe::class);
    }

    public function lecturer(){
        return $this->belongsTo(Lecturer::class);
    }

    public function venue(){
        return $this->belongsTo(Venue::class);
    }

    public function attendance(){
        return $this->hasMany(Attendance::class);
    }
}
