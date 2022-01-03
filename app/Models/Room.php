<?php

namespace App\Models;

use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Services\SpotifyService;

class Room extends Model
{
    public $spotify;

    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'user_id',
        'password',
        'playlist_id',
        'max_vote'
    ];

    protected static function boot()
    {
        parent::boot();

        static::retrieved(function($model)
        {
            $model->spotify = new SpotifyService($model->user->refresh);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admins()
    {
        return $this->belongsToMany(User::class, 'admins');
    }

    private function addInPlaylist($uri)
    {
        $offset = $this->spotify->findOffsetInPlaylist($this->playlist_id, $uri);

        if ($offset === null)
        {
            return $this->spotify->addToPlaylist($this->playlist_id, $uri);
        }

        $track_playing = $this->spotify->currentlyPlaying();

        if (!$track_playing)
        {
            return false;
        }

        $playing_offset = $this->spotify->findOffsetInPlaylist($this->playlist_id, $track_playing['uri']);

        if ($offset >= $playing_offset)
        {
            return false;
        }

        $this->spotify->removeFromPlaylist($this->playlist_id, $uri);
        return $this->spotify->addToPlaylist($this->playlist_id, $uri);
    }

    public function getNextTracks($max = 10)
    {
        $track_playing = $this->spotify->currentlyPlaying();

        if (!$track_playing)
        {
            return null;
        }

        $playing_offset = $this->spotify->findOffsetInPlaylist($this->playlist_id, $track_playing['uri']);
        return $this->spotify->getNextTracks($this->playlist_id, $playing_offset, $max);
    }

    public function voteSkip()
    {
        $track_playing = $this->spotify->currentlyPlaying();

        if (!$track_playing)
        {
            return;
        }

        if ($this->last_voted != $track_playing['uri'])
        {
            $this->last_voted = $track_playing['uri'];
            $this->vote_nb = 0;
        }

        ++$this->vote_nb;

        if ($this->vote_nb == $this->max_vote)
        {
            $this->vote_nb = 0;
        }

        $this->save();

        if ($this->vote_nb == 0)
        {
            $this->spotify->skipTrack();
        }
    }

    public function createGuest()
    {
        $guest = new Guest;
        $guest->name = Guest::generateName();
        $guest->room_id = $this->id;

        $guest->save();
        return $guest;
    }

    public function addMusic($uri, $guest_id)
    {
        if (!Guest::where('id', '=', $guest_id)
                ->where('room_id', '=', $this->id)
                ->exists())
        {
            return false;
        }

        if (!$this->addInPlaylist($uri))
        {
            return false;
        }

        $music = Music::where('uri', '=', $uri)
                ->where('room_id', '=', $this->id)
                ->first();

        if (!$music)
        {
            $music = new Music;
            $music->room_id = $this->id;
            $music->guest_id = $guest_id;
            $music->uri = $uri;
        }
        else
        {
            $music->guest_id = $guest_id;
        }

        $music->save();
        return true;
    }

    public function removeMusic($uri)
    {
        $music = Music::where('uri', '=', $uri)
                ->where('room_id', '=', $this->id)
                ->first();

        if ($music)
        {
            $music->delete();
        }

        $track_playing = $this->spotify->currentlyPlaying();

        if ($track_playing && $track_playing['uri'] == $uri)
        {
            $this->spotify->skipTrack();
        }

        return $this->spotify->removeFromPlaylist($this->playlist_id, $uri);
    }

    public function banGuest($guest_id)
    {
        $guest = Guest::where('id', '=', $guest_id)
                ->where('room_id', '=', $this->id)
                ->first();

        if (!$guest)
        {
            return false;
        }

        $musics = Music::where('guest_id', '=', $guest_id)->get();

        foreach ($musics as $music)
        {
            $this->removeMusic($music->uri);
        }

        $guest->delete();
        return true;
    }

    public function addAdmin($username)
    {
        $user = User::where('username', '=', $username)->first();

        if (!$user)
        {
            return false;
        }

        if ($user->isAdmin($this))
        {
            return false;
        }

        $user->roomsWhereAdmin()->attach($this->id);
    }
}
