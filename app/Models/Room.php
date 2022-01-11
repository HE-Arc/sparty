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

    /**
     * Create the Spotify Service at boot
     */
    protected static function boot()
    {
        parent::boot();

        static::retrieved(function($model)
        {
            $model->spotify = new SpotifyService($model->user->refresh);
        });
    }

    /**
     * Return the user owning this room
     * @return BelongsTo the owner
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Return the admins of this room
     * @return BelongsToMany the admins
     */
    public function admins()
    {
        return $this->belongsToMany(User::class, 'admins');
    }

    /**
     * Add the given track in the playlist of the room, at the right place
     * @param string $uri the uri of the track
     * @return bool whether the track was added
     */
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

    /**
     * Return the next tracks of the playlist
     * @param string $max the max number of tracks
     * @return array the list of tracks
     */
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

    /**
     * Vote to skip and skip the track if necessery
     */
    public function voteSkip()
    {
        if ($this->max_vote == -1)
        {
            Session::flash('status', 'Vote skip is blocked');
            return;
        }

        $track_playing = $this->spotify->currentlyPlaying();

        if (!$track_playing)
        {
            Session::flash('status', 'Music is not playing');
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
            Session::flash('status', 'Music skipped');

            return;
        }

        Session::flash('status', $this->max_vote - $this->vote_nb . ' vote(s) left to skip');
    }

    /**
     * Create a guest linked to this room
     * @return Guest the new guest
     */
    public function createGuest()
    {
        $guest = new Guest;
        $guest->name = Guest::generateName();
        $guest->room_id = $this->id;

        $guest->save();
        return $guest;
    }

    /**
     * Add the track in the playlist and link it to the guest
     * @param string $uri the uri of the track
     * @param int $guest_id the id of the guest
     * @param bool $is_admin whether the user is admin
     * @return bool whether the track was added
     */
    public function addMusic($uri, $guest_id, $is_admin)
    {
        if ($is_admin)
        {
            if (!$this->addInPlaylist($uri))
            {
                Session::flash('status', 'There was an error with Spotify');
                return false;
            }

            Session::flash('status', 'Music added');
            return true;
        }

        if (!Guest::where('id', '=', $guest_id)
                ->where('room_id', '=', $this->id)
                ->exists())
        {
            Session::flash('status', 'Your are not allowed to add music to the room');
            return false;
        }

        if (!$this->addInPlaylist($uri))
        {
            Session::flash('status', 'There was an error with Spotify');
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
        Session::flash('status', 'Music added');

        return true;
    }

    /**
     * Remove the music from the playlist and the music table
     * @param string $uri the uri of the track
     * @return bool whether the track was removed
     */
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

        Session::flash('status', 'Music removed');
        return $this->spotify->removeFromPlaylist($this->playlist_id, $uri);
    }

    /**
     * Ban the given guest and remove the music they added
     * @param int $guest_id the id of the guest
     * @return bool whether the guest was banned
     */
    public function banGuest($guest_id)
    {
        $guest = Guest::where('id', '=', $guest_id)
                ->where('room_id', '=', $this->id)
                ->first();

        if (!$guest)
        {
            Session::flash('status', 'User doesn\'t exist');
            return false;
        }

        $musics = Music::where('guest_id', '=', $guest_id)->get();

        foreach ($musics as $music)
        {
            $this->removeMusic($music->uri);
        }

        $guest->delete();
        Session::flash('status', 'User banned');

        return true;
    }

    /**
     * Add the given user as admin of this room
     * @param string $username the username of the user
     * @return bool whether the admin was added
     */
    public function addAdmin($username)
    {
        $user = User::where('username', '=', $username)->first();

        if (!$user)
        {
            Session::flash('status', 'User doesn\'t exist');
            return false;
        }

        if ($user->isAdmin($this))
        {
            Session::flash('status', 'User is already admin');
            return false;
        }

        $user->roomsWhereAdmin()->attach($this->id);
        Session::flash('status', 'Admin added');
    }
}
