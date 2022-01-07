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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if (!Session::has('room_id'))
        {
            Session::flash('status', "User doesn't have any room id in the session !");
            return Redirect::route('room.create');
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
                'canVote' => $room->max_vote != -1
            ]);
        }

    }

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
            'roomname' => $room->name
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
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
            'status' => Session::get('status')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
        $user = User::where('username', '=', $username)->first(); // TODO test exist
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
        $playlist_id = $spotify->createPlaylist('Sparty ' . $request->roomname); // TODO test not null
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
        Session::flash('status', "Room is created !");

        //@TODO regarder sur internet
        return Redirect::route('room.index');
    }

    /**
     * Add music to the playlist
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function addMusic(Request $request)
    {
        $errorMsg = '';

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

        if (!$errorMsg=='')
        {
            Session::flash('status', $errorMsg);
            return Redirect::route('room.index');
        }

        $room_id = Session::get('room_id');
        $room = Room::find($room_id);

        if (!Session::has('guest_id'))
        {
            $guest_ID = 7; //@TODO
            $errorMsg = 'Guest has not id !';
        }
        else
        {
            $guest_ID = Session::get('guest_id');
        }

        if($room->addMusic($uri, $guest_ID))
        {
            Session::flash('status', 'success added');
            return Redirect::route('room.index');
        }
        else
        {
            Session::flash('status', 'Problem occurred while adding the music to the playlist !');
            return Redirect::route('room.index');
        }
    }

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
            Session::flash('status', 'already voted for this music'); //@TODO mettre dans success
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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

    public function checkRoom(Request $request)
    {
        $roomname = $request->input('roomname');
        $password = $request->input('password');
        $hash_password = null;

        if (Room::where('name', '=', $roomname)->exists())
        {
            $hash_password = Room::where('name', '=', $roomname)->first()->password;
        }

        if ($hash_password != null && Hash::check($password, $hash_password))
        {
            Session::push('room_id', Room::where('name', '=', $roomname)->first()->id);
        }
        else
        {
            Session::flash('status', 'Wrong name or password for the room!');
        }
        return Redirect::route('room.index');
    }

    public function joinRoom()
    {
        return inertia('Sparty/Room/JoinRoom', [
                'status' => Session::get('status'),
            ]);
    }
}
