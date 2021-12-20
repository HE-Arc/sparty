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

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$books = Book::with('author')->latest()->paginate(5);
        Session::put('room_id', 13);

        if (!Session::has('room_id'))
        {
            Session::flash('status', "");
            return Redirect::route('room.create'); //@TODO room.home
        }
        else
        {
            $room_id = Session::get('room_id');
            $room = Room::find($room_id);

            return inertia('Sparty/Room/Index', [
                'roomname' => $room->name,
            ]);
        }

    }

    public function search(Request $request)
    {

        Session::put('room_id', 13);
        $room_id = Session::get('room_id');
        $room = Room::find($room_id);

        $refresh = $room->user->refresh;

        $trackname = $request->input('search');
        $spotify = new SpotifyService($refresh);

        $tab = $spotify->searchTrack($trackname);

        return Inertia::render('Sparty/Room/SearchResult', [
            'trackArray' => $tab,
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Sparty/Room/CreateRoom', [
            'status' => Session::get('status'),
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
        ]);

        if(Room::where('name', '=', $request->roomname)->exists())
        {
            Session::flash('status', "Name already exist!");
            return Redirect::route('room.create');
        }

        //@TODO
        $username = Session::get('username');
        $user = User::where('username', '=', $username)->first(); // TODO test exist
        if ($user == '')
        {
            Session::flash('status', "User not connected !");
            return Redirect::route('user.index');
        }
        $id = $user->id;

        $spotify = new SpotifyService($user->refresh);
        $playlist_id = $spotify->createPlaylist('Sparty ' . $request->roomname); // TODO test not null
        if ($playlist_id == ''){
            Session::flash('status', "User has not linked his Spotify account!");
            return Redirect::route('user.index');
        }

        $room = Room::create([
            'name' => $request->roomname,
            'user_id' => $id,
            'password' => Hash::make($request->password),
            'playlist_id' => $playlist_id,
            'max_vote' => 10
        ]);

        event(new Registered($room));

        Session::forget('room_id');
        Session::put('room_id', $room->id);
       // Session::flash('success', "Room is created !");

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

        //@TODO verification
        $uri = $request->uri;
        var_dump($uri);
        var_dump($request->all);
        $room_id = Session::get('room_id');
        $room = Room::find($room_id);
        $guest_ID = 4; //@TODO Sortir de la session
        var_dump($guest_ID);
        //$room->addMusic($uri, $guest_ID);
        //return Redirect::route('room.index');
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
        //
    }
}
