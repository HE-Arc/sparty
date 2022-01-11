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
     * Return the rooms where the user is admin
     * @return BelongsToMany the rooms where admin
     */
    public function roomsWhereAdmin()
    {
        return $this->belongsToMany(Room::class, 'admins');
    }

    /**
     * Check if the user is admin of the given room
     * @param Room $room the room to check
     * @return bool whether the user is admin
     */
    public function isAdmin($room)
    {
        if ($room->user_id == $this->id)
        {
            return true;
        }

        return $this->roomsWhereAdmin()->find($room->id) != null;
    }
}
