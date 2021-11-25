<?php

namespace App\Models;

use App\Services\SpotifyService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function addInPlaylist($uri)
    {
        $offset = $this->spotify->findOffsetInPlaylist($this->playlist_id, $uri);

        if ($offset == null)
        {
            return $this->spotify->addToPlaylist($this->playlist_id, $uri);
        }

        $uri_playing = $this->spotify->currentlyPlaying()['uri'];

        if (!$uri_playing)
        {
            return false;
        }

        $playing_offset = $this->spotify->findOffsetInPlaylist($this->playlist_id, $uri_playing);

        if ($offset >= $playing_offset)
        {
            return false;
        }

        $this->spotify->removeFromPlaylist($this->playlist_id, $uri);
        return $this->spotify->addToPlaylist($this->playlist_id, $uri);
    }

    public function getNextTracks($max = 10)
    {
        $uri_playing = $this->spotify->currentlyPlaying()['uri'];
        
        if (!$uri_playing)
        {
            return null;
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
        }
    }
}
