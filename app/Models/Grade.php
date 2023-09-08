<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;


    public function course(){
        return $this->hasMany(Course::class);
    }

    public function section(){
        return $this->hasMany(Section::class);
    }
}
