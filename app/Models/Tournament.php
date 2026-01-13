<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tournament extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function gameweeks()
    {
        return $this->hasMany(Gameweek::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
