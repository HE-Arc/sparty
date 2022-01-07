<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'username',
        'password',
        'refresh'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    // protected $hidden = [
    //     'password',
    //     'remember_token',
    // ];

    public function roomsWhereAdmin()
    {
        return $this->belongsToMany(Room::class, 'admins');
    }

    public function isAdmin($room)
    {
        if ($room->user_id == $this->id)
        {
            return true;
        }

        return $this->roomsWhereAdmin()->find($room->id) != null;
    }
}
