<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Services\SpotifyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use GuzzleHttp\Psr7\Uri;
use App\Jobs\Heartbeat;

class RoomController extends Controller
{
    /**
     * Display the room page or redirect if the room not exist
     *
     * @return Response the view or a redirection
     */
    public function index()
    {

        if (!Session::has('room_id'))
        {
            Session::flash('status', 'User doesn\'t have any room id in the session !');
            return Redirect::route('joinRoom');
        }
        else
        {
            $room_id = Session::get('room_id');
            $room = Room::find($room_id);

            if(!$room)
            {
                Session::flash('status', "Room was deleted !");
                return Redirect::route('room.create');
            }

            $refresh = $room->user->refresh;
            $spotify = new SpotifyService($refresh);
            $currentlyPlaying = $spotify->currentlyPlaying();
            $nextTrack = $room->getNextTracks(2);

            if ($currentlyPlaying == null)
            {
                Session::flash('status', "the room does not play any music !");
                $currentlyPlaying = '';
            }

            if ($nextTrack)
            {
                if (count($nextTrack) == 1)
                {
                    $nextTrack[1] = '';
                    $nextTrack[2] = '';
                }
                else if (count($nextTrack) == 2)
                {
                    $nextTrack[2] = '';
                }
            }
            else
            {
                $nextTrack[1] = '';
                $nextTrack[2] = '';
            }

            $isAdmin = false;
            $username = Session::get("username");

            if ($username)
            {
                $user = User::where('username', '=', $username)->first();

                if ($user)
                {
                    $isAdmin = $user->isAdmin($room);
                }
            }

            return Inertia::render('Sparty/Room/Index', [
                'status' => Session::get('status'),
                'roomname' => $room->name,
                'currentPlaying' => $currentlyPlaying,
                'nextTrack' => $nextTrack,
                'roomid' => $room_id,
                'isAdmin' => $isAdmin,
                'canVote' => $room->max_vote != -1,
                'username' => Session::get('username')
            ]);
        }
    }

    /**
     * Search the music with sportify api and check if the room exist with room_id in the request
     * @param Request $request the request
     * @return Response the redirection
     */
    public function search(Request $request)
    {

        $room_id = Session::get('room_id');
        $room = Room::find($room_id);
        if(!$room)
        {
            Session::flash('status', "Room was deleted !");
            return Redirect::route('room.create');
        }

        $refresh = $room->user->refresh;

        $trackname = $request->input('search');
        $spotify = new SpotifyService($refresh);

        $tab = $spotify->searchTrack($trackname, 0, 36);

        return Inertia::render('Sparty/Room/SearchResult', [
            'trackArray' => $tab,
            'roomname' => $room->name,
            'username' => Session::get('username')
            ]);
    }

    /**
    * Display the admin page for creating new room
    *
    * @return Response the redirection
    */
    public function create()
    {
        $username = Session::get('username');
        $user = User::where('username', '=', $username)->first();
        if ($user == null)
        {
            Session::flash('status', "User not connected !");
            return Redirect::route('user.index');
        }

        return Inertia::render('Sparty/Room/CreateRoom', [
            'status' => Session::get('status'),
            'username' => Session::get('username')
        ]);
    }

    /**
     * Store a newly created room in database and check the request.
     * Create a playlist in Sportify with the api
     *
     * @param Request $request the request
     * @return Response the redirection
     */
    public function store(Request $request)
    {
        $request->validate([
            'roomname' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'vote'     => 'required|numeric|max:100'
        ]);

        if(Room::where('name', '=', $request->roomname)->exists())
        {
            Session::flash('status', "Name already exist!");
            return Redirect::route('room.create');
        }

        $username = Session::get('username');
        $user = User::where('username', '=', $username)->first();
        if ($user == null)
        {
            Session::flash('status', "User not connected !");
            return Redirect::route('user.index');
        }

        $room = Room::where('user_id', '=', $user->id)->first();

        if ($room)
        {
            Session::flash('status', "User has already a room !");
            Session::put('room_id', $room->id);
            return Redirect::route('room.index');
        }

        $spotify = new SpotifyService($user->refresh);
        $playlist_id = $spotify->createPlaylist('Sparty ' . $request->roomname);
        if ($playlist_id == null) {
            Session::flash('status', "User has not linked his Spotify account!");
            return Redirect::route('user.index');
        }

        $room = Room::create([
            'name' => $request->roomname,
            'user_id' => $user->id,
            'password' => Hash::make($request->password),
            'playlist_id' => $playlist_id,
            'max_vote' => $request->vote
        ]);

        event(new Registered($room));

        Session::forget('room_id');
        Session::put('room_id', $room->id);
        Session::put('guest_id', $room->createGuest()->id);
        Session::flash('status', "Room is created !");

        return Redirect::route('room.index');
    }

    /**
     * Add music to the playlist
     *
     * @param Request $request the request
     * @return Response the redirection
     */
    public function addMusic(Request $request)
    {
        $errorMsg = '';
        $guest_ID = -1;

        $request->validate([
            'uri' => 'required'
        ]);

        $uri = $request->uri;
        if ($uri == '')
        {
            $errorMsg = 'Music link not found !';
        }

        if (!Session::has('room_id'))
        {
            $errorMsg = 'Room not found !';
        }

        if(!Room::where('id', '=', Session::get('room_id'))->exists())
        {
            $errorMsg = 'Room not found !';
        }

        $room_id = Session::get('room_id');
        $room = Room::find($room_id);

        $user = User::where('username', '=', Session::get('username'))->first();

        if ($user && $user->isAdmin($room))
        {
            $room->addMusic($uri, -1, true);
            return Redirect::route('room.index');
        }

        if (!Session::has('guest_id'))
        {
            $errorMsg = 'You did not join the room correctly!';
        }
        else
        {
            $guest_ID = Session::get('guest_id');
        }

        if ($errorMsg != '')
        {
            Session::flash('status', $errorMsg);
            return Redirect::route('room.index');
        }

        $room->addMusic($uri, $guest_ID, false);
        return Redirect::route('room.index');
    }

    /**
    * Enables to get the vote from the request and to control and add the vote on the current music
    *
    * @param Request $request the request
    * @return Response the redirection
    */
    public function vote(Request $request){

        $room_id = Session::get('room_id');
        $room = Room::find($room_id);
        if(!$room)
        {
            Session::flash('status', "Room doesn't exist !");
            return Redirect::route('room.create');
        }

        if (Session::get('music_voted') == $request->currentPlaying['uri'])
        {
            Session::flash('status', 'Already voted for this music');
        }
        else
        {
            $room->voteSkip();
            Session::forget('music_voted');
            Session::put('music_voted', $request->currentPlaying['uri']);
        }

        return Redirect::route('room.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
    * Remove the specified room from database with the room_id
    *
    * @param int the room id
    * @return Response the redirection
    */
    public function destroy($id)
    {
        $room_id = $id;
        $room = Room::find($room_id);

        if(Room::where('id', '=', Session::get('room_id'))->exists())
        {
            Session::flash('status', 'room is deleted');
            $room->delete();
            Session::forget('room_id');
            return Redirect::route('room.create');
        }
        else
        {
            Session::flash('status', 'problem with the delete room !');
            return Redirect::route('room.index');
        }

    }

    /**
    * Check if the connected user can join the room
    *
    * @param Request $request the request
    * @return Response the redirection
    */
    public function checkRoom(Request $request)
    {
        $user = User::where('username', '=', Session::get('username'))->first();

        $roomname = $request->input('roomname');
        $room = Room::where('name', '=', $roomname)->first();
        $password = $request->input('password');
        $hash_password = null;

        if ($room != null)
        {
            if (Session::has('room_id'))
            {
                if (Session::get('room_id') == $room->id)
                {
                    return Redirect::route('room.index');
                }
            }

            $hash_password = $room->password;
            $room_id = $room->id;
        }
        else
        {
            Session::flash('status', 'Wrong name or password for the room!');
            return Redirect::route('joinRoom');
        }

        if ($hash_password != null && Hash::check($password, $hash_password))
        {
            $is_admin = $user != null && $user->isAdmin($room);

            if($room->can_join || $is_admin)
            {
                Session::put('room_id', $room_id);

                if ($is_admin)
                {
                    return Redirect::route('room.index');
                }
            }
            else
            {
                Session::flash('status', 'The room is not open for you!');
                return Redirect::route('joinRoom');
            }

        }
        else
        {
            Session::flash('status', 'Wrong name or password for the room!');
            return Redirect::route('joinRoom');
        }

        Session::put('guest_id', $room->createGuest()->id);
        return Redirect::route('room.index');
    }

    /**
    * Redirect on room
    *
    * @return Response the redirection
    */
    public function joinRoom()
    {
        return inertia('Sparty/Room/JoinRoom', [
                'status' => Session::get('status'),
                'username' => Session::get('username')
            ]);
    }
}
