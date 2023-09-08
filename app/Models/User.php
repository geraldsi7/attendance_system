<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'country_id',
        'email',
        'role',
        'phone',
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


    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function purchase()
    {
        return $this->hasMany(Purchase::class)->withTrashed();
    }

    public function weekly_pay()
    {
        return $this->hasMany(WeeklyPays::class)->withTrashed();
    }

    public function monthly_pay()
    {
        return $this->hasMany(MonthlyPays::class)->withTrashed();
    }

    public function insurance()
    {
        return $this->hasMany(Insurance::class);
    }

    public function tax()
    {
        return $this->hasMany(Tax::class);
    }
}
