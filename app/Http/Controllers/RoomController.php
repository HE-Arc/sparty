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
        return inertia('Sparty/Room/Index');
    }

    public function search(Request $request)
    {

        $trackname = $request->input('search');
        // $spotify = new SpotifyService('AQABx70EHUSdRmSalwLIWKoFQene74RV9OfeX6Ixczd9bvLc8uzhiqxSQChESYEn53JwYzlzFMD85-hZFo_AM8aRup4e8n6pLkySExiFTutsKzbpPfb-D-ZWAvtVrPJVWpc');

        return Inertia::render('Sparty/Room/Search', [
            'trackname' => $trackname,
            ]);
    }

    public function test()
    {
        return Redirect::route('search', ['trackname' => "hello",]);
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
        $id = $user->id;

        $spotify = new SpotifyService($user->refresh);
        $playlist_id = $spotify->createPlaylist('Sparty ' . $request->roomname); // TODO test not null

        $room = Room::create([
            'name' => $request->roomname,
            'user_id' => $id,
            'password' => Hash::make($request->password),
            'playlist_id' => $playlist_id
        ]);

        event(new Registered($room));

        Session::forget('roomname');
        Session::push('roomname', $request->roomname);
        Session::flash('success', "Room is created !");



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
        //
    }
}
