<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;

use App\Models\Guest;
use App\Models\Music;
use App\Models\User;
use App\Models\Room;

class AdminController extends Controller
{
    public function index()
    {
        $room = $this->getRoom();

        if (!$room)
        {
            return Redirect::route('room.create');
        }

        $nextTracks = $room->getNextTracks(20);

        if (!$nextTracks)
        {
            Session::flash('status', 'The room doesn\'t play music');
            return inertia('Sparty/Admin/Index', [
                'status' => Session::get('status'),
                'roomName' => $room->name,
                'nextTracks' => [],
                'canJoin' => $room->can_join
            ]);
        }

        for ($i = 0; $i < count($nextTracks); ++$i)
        {
            $uri = $nextTracks[$i]['uri'];
            $music = Music::where('uri', '=', $uri)
                    ->where('room_id', '=', $room->id)
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
            'nextTracks' => $nextTracks,
            'canJoin' => $room->can_join
        ]);
    }

    public function deleteTrack(Request $request)
    {
        $room = $this->getRoom();

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
        $room = $this->getRoom();

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

    public function addAdmin(Request $request)
    {
        $room = $this->getRoom();

        if (!$room)
        {
            return Redirect::route('admin');
        }

        if ($request->username)
        {
            $room->addAdmin($request->username);
        }

        return Redirect::route('admin');
    }

    public function lockRoom(Request $request)
    {
        $room = $this->getRoom();

        if (!$room)
        {
            return Redirect::route('admin');
        }

        if ($request->lock !== null)
        {
            $room->can_join = !$request->lock;
            $room->save();
        }

        return Redirect::route('admin');
    }

    public function playPlaylist()
    {
        $room = $this->getRoom();

        if (!$room)
        {
            return Redirect::route('admin');
        }

        $room->spotify->setShuffle();
        $room->spotify->playPlaylist($room->playlist_id);

        return Redirect::route('admin');
    }

    public function deleteRoom()
    {
        $room = $this->getRoom();

        if (!$room)
        {
            return Redirect::route('admin');
        }

        $room->delete();
        Session::forget('room_id');

        return Redirect::route('admin');
    }


    private function getRoom()
    {
        if (!Session::has('room_id'))
        {
            return null;
        }

        $room_id = Session::get('room_id');
        $room = Room::find($room_id);

        if (!$room)
        {
            Session::flash('status', 'Room was deleted');
            return null;
        }

        $username = Session::get("username");

        if (!$username)
        {
            Session::flash('status', 'You are not connected');
            return null;
        }

        $user = User::where('username', '=', $username)->first();

        if (!$user)
        {
            Session::flash('status', 'You are not connected');
            return null;
        }

        if (!$user->isAdmin($room))
        {
            Session::flash('status', 'You are not admin of the room');
            return null;
        }

        return $room;
    }
}
