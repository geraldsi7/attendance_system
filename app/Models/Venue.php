<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'longitude',
        'latitude'
    ];

    public function session(){
        return $this->hasMany(Session::class);
    }
}
