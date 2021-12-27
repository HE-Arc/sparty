<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

use App\Models\Guest;
use App\Models\Music;
use App\Models\Room;

class AdminController extends Controller
{
    public function index()
    {
        if (!Session::has('room_id'))
        {
            return Redirect::route('room.create');
        }

        $room_id = Session::get('room_id');
        $room = Room::find($room_id);

        if (!$room)
        {
            Session::flash('status', 'Room was deleted');
            return Redirect::route('room.create');
        }

        $nextTracks = $room->getNextTracks(20);

        if (!$nextTracks)
        {
            Session::flash('status', 'The room doesn\'t play music');
            return inertia('Sparty/Admin/Index', [
                'status' => Session::get('status'),
                'roomName' => $room->name,
                'nextTracks' => []
            ]);
        }

        for ($i = 0; $i < count($nextTracks); ++$i)
        {
            $uri = $nextTracks[$i]['uri'];
            $music = Music::where('uri', '=', $uri)
                    ->where('room_id', '=',$room_id)
                    ->first();

            if ($music)
            {
                $guest = Guest::find($music->guest_id);
                $nextTracks[$i]['guest_name'] = $guest->name;
                $nextTracks[$i]['guest_id'] = $guest->id;
            }
            else
            {
                $nextTracks[$i]['guest_name'] = "";
                $nextTracks[$i]['guest_id'] = "";
            }
        }

        return inertia('Sparty/Admin/Index', [
            'status' => Session::get('status'),
            'roomName' => $room->name,
            'nextTracks' => $nextTracks
        ]);
    }

    public function deleteTrack(Request $request)
    {
        if (!Session::has('room_id'))
        {
            return Redirect::route('admin');
        }

        $room_id = Session::get('room_id');
        $room = Room::find($room_id);

        if (!$room)
        {
            return Redirect::route('admin');
        }

        if ($request->uri)
        {
            $room->removeMusic($request->uri);
        }

        return Redirect::route('admin');
    }

    public function banGuest(Request $request)
    {
        if (!Session::has('room_id'))
        {
            return Redirect::route('admin');
        }

        $room_id = Session::get('room_id');
        $room = Room::find($room_id);

        if (!$room)
        {
            return Redirect::route('admin');
        }

        if ($request->guest_id)
        {
            $room->banGuest($request->guest_id);
        }

        return Redirect::route('admin');
    }
}
