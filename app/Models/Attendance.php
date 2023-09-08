<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

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
