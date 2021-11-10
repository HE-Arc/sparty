<?php

namespace App\Services;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use PhpParser\Node\Stmt\TryCatch;
use Ramsey\Uuid\Guid\Fields;

class SpotifyService
{
    private $id;
    private $secret;
    private $refresh;
    private $successful;

    public function __construct(string $refresh = null)
    {
        $this->id = config('sparty.spotify_id');
        $this->secret = config('sparty.spotify_secret');
        $this->refresh = $refresh;
        $this->successful = false;
    }

    public function isSuccessful()
    {
        return $this->successful;
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

    /**
     * Fetch the refresh token from  Spotify API
     * @param string $code the code received after the redirect
     * @return string the refresh token or null
     */
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
       
        try
        {
            $response = $client->request('POST', $endpoint, ['query' => $query, 'headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            $this->successful = false;
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (isset($results['refresh_token']))
        {
            $this->successful = true;
            return $results['refresh_token'];
        }

        $this->successful = false;
        return null;
    }

    private function getAccess()
    {
        $endpoint = 'https://accounts.spotify.com/api/token';

        $query = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refresh
        ];

        $headers = [
            'Authorization' => 'Basic ' . base64_encode($this->id . ':' . $this->secret),
            'Content-Type' => 'application/x-www-form-urlencoded'
        ];

        $client = new \GuzzleHttp\Client();
       
        try
        {
            $response = $client->request('POST', $endpoint, ['query' => $query, 'headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            $this->successful = false;
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (isset($results['access_token']))
        {
            $this->successful = true;
            return $results['access_token'];
        }

        $this->successful = false;
        return null;
    }

    public function addToQueue($uri)
    {
        $access = $this->getAccess();

        if (!$access)
        {
            $this->successful = false;
            return;
        }

        $endpoint = 'https://api.spotify.com/v1/me/player/queue';

        $query = [
            'uri' => $uri
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $access
        ];

        $client = new \GuzzleHttp\Client();
       
        try
        {
            $client->request('POST', $endpoint, ['query' => $query, 'headers' => $headers]);
            $this->successful = true;
        }
        catch (GuzzleException $e)
        {
            $this->successful = false;
            return;
        }
    }

    public function searchTrack($trackName, $tracksByPage = 12, $pageNb = 0)
    {
        $access = $this->getAccess();

        if (!$access)
        {
            $this->successful = false;
            return;
        }

        $endpoint = 'https://api.spotify.com/v1/search';

        $query = [
            'q' => $trackName,
            'type' => 'track',
            'limit' => $tracksByPage,
            'offset' => $tracksByPage * $pageNb
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $access
        ];

        $client = new \GuzzleHttp\Client();

        try
        {
            $response = $client->request('GET', $endpoint, ['query' => $query, 'headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            $this->successful = false;
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (!isset($results['tracks']['items']))
        {
            $this->successful = false;
            return $results['tracks"]["items'];
        }

        $toReturn = [];

        foreach ($results['tracks']['items'] as $track)
        {
            array_push($toReturn, [
                'uri' => $track['uri'],
                'image' => $track['album']['images'][1]['url'],
                'name' => $track['name'],
                'artist' => $track['artists'][0]['name']
            ]);
        }

        $this->successful = true;
        return $toReturn;
    }
}