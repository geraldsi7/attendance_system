<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id',
        'student_id',
    ];

    public function session(){
        return $this->belongsTo(Session::class);
    }

    public function student(){
        return $this->belongsTo(Student::class);
    }
}
