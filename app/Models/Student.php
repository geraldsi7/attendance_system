<?php

namespace App\Models;

use App\Notifications\sendStudentPasswordResetNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guard = 'student';
    protected $fillable = [
        'name',
        'email',
        'student_id',
        'index_number',
        'classe_id',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function classe()
    {
        return $this->belongsTo(Classe::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new sendStudentPasswordResetNotification($token));
    }
}
