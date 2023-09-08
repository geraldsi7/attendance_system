<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;


    public function grade(){
        return $this->belongsTo(Grade::class);
    }

    public function student(){
        return $this->hasMany(Student::class);
    }

    public function session(){
        return $this->hasMany(Session::class);
    }
}
