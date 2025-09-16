<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classe extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'level_id',
        'section_id'
    ];

    public function session()
    {
        return $this->hasMany(Session::class);
    }

    public function student()
    {
        return $this->hasMany(Student::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function lecturer()
    {
        return $this->belongsToMany(Lecturer::class);
    }

    public function course()
    {
        return $this->belongsToMany(Course::class);
    }
}
