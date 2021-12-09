<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * A service class to use the Spotify API
 */
class SpotifyService
{
    private $api_id;
    private $api_secret;

    private $user_id;
    private $refresh;
    private $access;

    private $client;

    /**
     * Construct the SpotifyService
     * @param string $refresh the refresh code of the user
     */
    public function __construct($refresh = null)
    {
        $this->api_id = config('sparty.spotify_id');
        $this->api_secret = config('sparty.spotify_secret');

        $this->client = new Client();

        $this->refresh = $refresh;
        $this->access = null;

        if ($refresh)
        {
            $this->access = $this->getAccess();
            $this->user_id = $this->getUserId();
        }
    }

    /**
     * Redirect to the Spotify connection page
     * @return \Illuminate\Http\RedirectResponse the redirecton to the Spotify connection
     */
    public function redirect()
    {
        $redirect = route('code');
        $scope = "user-modify-playback-state+user-read-currently-playing+user-read-playback-state+playlist-modify-private";

        $url = url("https://accounts.spotify.com/authorize?client_id={$this->api_id}"
                . "&response_type=code&redirect_uri={$redirect}"
                . "&scope={$scope}");

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
            'Authorization' => 'Basic ' . base64_encode($this->api_id . ':' . $this->api_secret),
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
            $this->user_id = $this->getUserId();

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
            'Authorization' => 'Basic ' . base64_encode($this->api_id . ':' . $this->api_secret),
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
     * Fetch the id of the user associated to the refresh code
     * @return string|null the user id or null if failed
     */
    private function getUserId()
    {
        if (!$this->access)
        {
            return null;
        }

        $endpoint = 'https://api.spotify.com/v1/me';

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

        if (isset($results['id']))
        {
            return $results['id'];
        }

        return null;
    }

    /**
     * Fetch the name of the user associated to the refresh code
     * @return string|null the username or null if failed
     */
    public function getUserName()
    {
        if (!$this->access)
        {
            return null;
        }

        $endpoint = 'https://api.spotify.com/v1/me';

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

        if (isset($results['display_name']))
        {
            $this->user_id = $results['id'];
            return $results['display_name'];
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
     * @param int $pageNb the page of the search
     * @param int $tracksByPage the number of tracks per page
     * @return array|null list of tracks found or null if failed
     */
    public function searchTrack($trackName, $pageNb = 0, $tracksByPage = 12)
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
        if (!$this->access)
        {
            return null;
        }

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

    /**
     * Create a new playlist with a given name
     * @param string $name the name of the playlist
     * @return string|null the id of the playlist or null if failed
     */
    public function createPlaylist($name)
    {
        if (!$this->access || !$this->user_id)
        {
            return null;
        }

        $endpoint = url("https://api.spotify.com/v1/users/{$this->user_id}/playlists");

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        $json = [
            'name' => $name,
            'public' => 'false'
        ];

        try
        {
            $response = $this->client->request('POST', $endpoint,
                    ['headers' => $headers, 'json' => $json]);
        }
        catch (GuzzleException $e)
        {
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (isset($results['id']))
        {
            return $results['id'];
        }

        return null;
    }

    /**
     * Fetch the uri of the playlist given
     * @param string $playlist_id the id of the playlist
     * @return string|null the uri of the playlist or null if failed
     */
    private function getPlaylistUri($playlist_id)
    {
        if (!$this->access)
        {
            return null;
        }

        $endpoint = url("https://api.spotify.com/v1/playlists/{$playlist_id}");

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

        if (isset($results['uri']))
        {
            return $results['uri'];
        }

        return null;
    }

    /**
     * Find the offset the track in the given playlist
     * @param string $playlist_id the id of the playlist
     * @param string $track_uri the uri of the track
     * @return int|null the offset of the the track or null if failed
     */
    public function findOffsetInPlaylist($playlist_id, $track_uri)
    {
        if (!$this->access)
        {
            return null;
        }

        $offset = 0;

        while (true)
        {
            $tracks = $this->getNextTracks($playlist_id, $offset, 50);

            if (count($tracks) == 0)
            {
                return null;
            }

            foreach ($tracks as $track)
            {
                if ($track['uri'] == $track_uri)
                {
                    return $offset;
                }

                ++$offset;
            }
        }
    }

    /**
     * Return the next n tracks of the playlist after the offset
     * @param string $playlist_id the id of the playlist
     * @param int $offset the offset in the playlist
     * @param int $max the max number of tracks to return, <= 50
     * @return array|null the next tracks or null if failed
     */
    public function getNextTracks($playlist_id, $offset = 0, $max = 10)
    {
        if (!$this->access)
        {
            return null;
        }

        $endpoint = url("https://api.spotify.com/v1/playlists/{$playlist_id}/tracks");

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        $query = [
            'offset' => $offset,
            'max' => $max
        ];

        try
        {
            $response = $this->client->request('GET', $endpoint,
                    ['headers' => $headers, 'query' => $query]);
        }
        catch (GuzzleException $e)
        {
            return null;
        }

        $results = json_decode($response->getBody(), true);

        if (!isset($results['items']))
        {
            return null;
        }

        $toReturn = [];

        foreach ($results['items'] as $item)
        {
            array_push($toReturn, $this->itemToArray($item['track']));
        }

        return $toReturn;
    }

    /**
     * Add the given track at the end of the playlist
     * @param string $playlist_id the id of the playlist
     * @param string $track_uri the uri of the track to be added
     * @return bool whether the track was added
     */
    public function addToPlaylist($playlist_id, $track_uri)
    {
        if (!$this->access)
        {
            return false;
        }

        $endpoint = url("https://api.spotify.com/v1/playlists/{$playlist_id}/tracks");

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        $json = [
            'uris' => [$track_uri]
        ];

        try
        {
            $this->client->request('POST', $endpoint,
                    ['headers' => $headers, 'json' => $json]);
        }
        catch (GuzzleException $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Play the given playlist
     * @param string $playlist_id the id of the playlist
     * @return bool whether the playlist was played
     */
    public function playPlaylist($playlist_id)
    {
        if (!$this->access)
        {
            return false;
        }

        $uri = $this->getPlaylistUri($playlist_id);

        if (!$uri)
        {
            return false;
        }

        $endpoint = 'https://api.spotify.com/v1/me/player/play';

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        $json = [
            'context_uri' => $uri
        ];

        try
        {
            $this->client->request('PUT', $endpoint,
                    ['headers' => $headers, 'json' => $json]);
        }
        catch (GuzzleException $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Set the currently playing flux shuffle mode
     * @param bool $shuffle whether to shuffle or not
     * @return bool whether the shuffle mode was set
     */
    public function setShuffle($shuffle = false)
    {
        if (!$this->access)
        {
            return false;
        }

        $endpoint = url("https://api.spotify.com/v1/me/player/shuffle");

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        $query = [
            'state' => var_export($shuffle, true)
        ];

        try
        {
            $this->client->request('PUT', $endpoint,
                    ['headers' => $headers, 'query' => $query]);
        }
        catch (GuzzleException $e)
        {
            return false;
        }

        return true;
    }

    /**
     * Remove the given track from the playlist
     * @param string $playlist_id the id of the playlist
     * @param string $track_uri the uri of the track to remove
     * @return bool whether the track was removed
     */
    public function removeFromPlaylist($playlist_id, $track_uri)
    {
        if (!$this->access)
        {
            return false;
        }

        $endpoint = url("https://api.spotify.com/v1/playlists/{$playlist_id}/tracks");

        $headers = [
            'Authorization' => 'Bearer ' . $this->access
        ];

        $json = [
            'tracks' => [[
                'uri' => $track_uri
            ]]
        ];

        try
        {
            $this->client->request('DELETE', $endpoint,
                    ['headers' => $headers, 'json' => $json]);
        }
        catch (GuzzleException $e)
        {
            return false;
        }

        return true;
    }
}
