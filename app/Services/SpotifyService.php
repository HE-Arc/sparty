<?php

namespace App\Services;

use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Ramsey\Uuid\Guid\Fields;

class SpotifyService
{
    private $id;
    private $secret;
    private $refresh;

    public function __construct(string $refresh = null)
    {
        $this->id = config('sparty.spotify_id');
        $this->secret = config('sparty.spotify_secret');
        $this->refresh = $refresh;
    }

    /**
     * Redirect to the Spotify connection page
     */
    public function redirect()
    {
        $redirect = route('code');
        
        $url = url("https://accounts.spotify.com/authorize?client_id={$this->id}"
                . "&response_type=code&redirect_uri={$redirect}"
                . "&scope=user-modify-playback-state+user-read-currently-playing+user-read-playback-state");

        return redirect()->away($url);
    }

    public function getRefresh($code)
    {
        $endpoint = 'https://accounts.spotify.com/api/token';

        $query = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => route('code')
        ];

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->id . ':' . $this->secret),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $client = new \GuzzleHttp\Client();

        $response = $client->request('POST', $endpoint, ['query' => $query, 'headers' => $headers]);
        print($response->getBody());
    }
}