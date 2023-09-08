<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    public function lecturer(){
        return $this->belongsTo(Lecturer::class);
    }

    public function grade(){
        return $this->belongsTo(Grade::class);
    }

    public function session(){
        return $this->hasMany(Session::class);
    }
}
