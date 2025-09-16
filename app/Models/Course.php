<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code'
    ];

    public function lecturer(){
        return $this->belongsToMany(Lecturer::class);
    }

    public function classe()
    {
        return $this->belongsToMany(Classe::class);
    }

    public function session()
    {
        return $this->hasMany(Session::class);
    }
}
