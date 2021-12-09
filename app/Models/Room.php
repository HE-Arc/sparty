<?php

namespace App\Models;

use App\Services\SpotifyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Room extends Model
{
    public $spotify;

    use HasFactory;

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

    private function addInPlaylist($uri)
    {
        $offset = $this->spotify->findOffsetInPlaylist($this->playlist_id, $uri);

        if ($offset === null)
        {
            if ($this->spotify->addToPlaylist($this->playlist_id, $uri))
            {
                Session::flash('success', 'Track was added');
            }
            else
            {
                Session::flash('status', 'Track could not be added, try again later');
            }
        }

        $uri_playing = $this->spotify->currentlyPlaying()['uri'];

        if (!$uri_playing)
        {
            Session::flash('status', 'Spotify is not playing');
            return false;
        }

        $playing_offset = $this->spotify->findOffsetInPlaylist($this->playlist_id, $uri_playing);

        if ($offset >= $playing_offset)
        {
            Session::flash('status', 'Track already added');
            return false;
        }

        $this->spotify->removeFromPlaylist($this->playlist_id, $uri);

        if ($this->spotify->addToPlaylist($this->playlist_id, $uri))
        {
            Session::flash('success', 'Track was added');
        }
        else
        {
            Session::flash('status', 'Track could not be added, try again later');
        }
    }

    public function getNextTracks($max = 10)
    {
        $uri_playing = $this->spotify->currentlyPlaying()['uri'];
        
        if (!$uri_playing)
        {
            Session::flash('status', 'Could not search, try again later');
        }

        $playing_offset = $this->spotify->findOffsetInPlaylist($this->playlist_id, $uri_playing);
        return $this->spotify->getNextTracks($this->playlist_id, $playing_offset, $max);
    }

    public function voteSkip()
    {
        ++$this->vote_nb;

        if ($this->vote_nb == $this->max_vote)
        {
            $this->vote_nb = 0;
        }

        $this->save();

        if ($this->vote_nb == 0)
        {
            $this->spotify->skipTrack();
            Session::flash('success', 'Track skipped');
        }
        else
        {
            Session::flash('success', $this->vote_nb + " vote(s) remaining");
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
        $guest = Guest::where('id', '=', $guest_id)
                ->where('room_id', '=', $this->id)
                ->first();

        if (!$guest)
        {
            Session::flash('status', 'You are not allowed to add music in this room');
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
}
