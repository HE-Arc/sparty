<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * A service class to use the Spotify API
 */
class SpotifyService
{
    private $id;
    private $secret;

    private $refresh;
    private $access;

    private $client;

    /**
     * Construct the SpotifyService
     * @param string $refresh the refresh code of the user
     */
    public function __construct(string $refresh = null)
    {
        $this->id = config('sparty.spotify_id');
        $this->secret = config('sparty.spotify_secret');

        $this->client = new Client();

        $this->refresh = $refresh;
        $this->access = null;

        if ($refresh)
        {
            $this->access = $this->getAccess();
        }
    }

    /**
     * Redirect to the Spotify connection page
     * @return \Illuminate\Http\RedirectResponse the redirecton to the Spotify connection
     */
    public function redirect()
    {
        $redirect = route('code');

        $url = url("https://accounts.spotify.com/authorize?client_id={$this->id}"
                . "&response_type=code&redirect_uri={$redirect}"
                . "&scope=user-modify-playback-state+user-read-currently-playing+user-read-playback-state");

        return $url;
    }

    /**
     * Fetch the refresh token from Spotify API
     * @param string $code the code received after the redirect
     * @return string|null the refresh token or null if failed
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

        try
        {
            $response = $this->client->request('POST', $endpoint,
                    ['query' => $query, 'headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (isset($results['refresh_token']))
        {
            $this->refresh = $results['refresh_token'];
            $this->access = $this->getAccess();

            return $results['refresh_token'];
        }

        return null;
    }

    /**
     * Fetch the access token from Spotify API using the refresh token
     * @return string|null the access token or null if failed
     */
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

        try
        {
            $response = $this->client->request('POST', $endpoint,
                    ['query' => $query, 'headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (isset($results['access_token']))
        {
            return $results['access_token'];
        }

        return null;
    }

    /**
     * Add the given track to the Spotify queue
     * @param string $uri the Spotify uri of the track
     * @return bool whether the track was added
     */
    public function addToQueue($uri)
    {
        if (!$this->access)
        {
            return false;
        }

        $endpoint = 'https://api.spotify.com/v1/me/player/queue';

        $query = [
            'uri' => $uri
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        try
        {
            $this->client->request('POST', $endpoint, ['query' => $query, 'headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Simplify the given Spotify item
     * @param array $item the Spotify item from the API
     * @return array the item as a more simple array
     */
    private function itemToArray($item)
    {
        return [
            'name' => $item['name'],
            'artist' => $item['artists'][0]['name'],
            'uri' => $item['uri'],
            'image' => $item['album']['images'][1]['url']
        ];
    }

    /**
     * Search for a given track on Spotify
     * @param string $trackName the name of the track
     * @param int $tracksByPage the number of tracks per page
     * @param int $tracksByPage the page of the search
     * @return array|null list of tracks found or null if failed
     */
    public function searchTrack($trackName, $tracksByPage = 12, $pageNb = 0)
    {
        if (!$this->access)
        {
            return null;
        }

        $endpoint = 'https://api.spotify.com/v1/search';

        $query = [
            'q' => $trackName,
            'type' => 'track',
            'limit' => $tracksByPage,
            'offset' => $tracksByPage * $pageNb
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        try
        {
            $response = $this->client->request('GET', $endpoint,
                    ['query' => $query, 'headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (!isset($results['tracks']['items']))
        {
            return null;
        }

        $toReturn = [];

        foreach ($results['tracks']['items'] as $item)
        {
            array_push($toReturn, $this->itemToArray($item));
        }

        return $toReturn;
    }

    /**
     * Skip the current track playing
     * @return bool whether the track was skipped
     */
    public function skipTrack()
    {
        if (!$this->access)
        {
            return false;
        }

        $endpoint = 'https://api.spotify.com/v1/me/player/next';

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        try
        {
            $this->client->request('POST', $endpoint, ['headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Show the track currently playing on Spotify
     * @return array|null the track currently playing or null if failed
     */
    public function currentlyPlaying()
    {
        $endpoint = 'https://api.spotify.com/v1/me/player/currently-playing';

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        try
        {
            $response = $this->client->request('GET', $endpoint, ['headers' => $headers]);
        }
        catch (GuzzleException $e)
        {
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (isset($results['item']))
        {
            return $this->itemToArray($results['item']);
        }

        return null;
    }
}
