<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SpotifyService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $result = null;
        $username = Session::get("username");

        if($username != null)
        {
            $username = $username[0];
            // $currentUser = User::where('username', '=', $username)->first();
            $result = compact('username');
            // print_r($result['currentUser']['username']);
            return inertia('Sparty/User/Index', $result);
        }

        return Inertia::render('Sparty/User/Login', [
            'canResetPassword' => false,
            'status' => Session::get('status'),
        ]);

    }

    public function checkLogin(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');
        $hash_password = null;

        if (User::where('username', '=', $username)->exists())
        {
            $hash_password = User::where('username', '=', $username)->first()->password;
        }

        if ($hash_password != null && Hash::check($password, $hash_password))
        {
            Session::push('username', $username);
        }
        else
        {
            Session::flash('status', 'Wrong username or password !');
        }
        return Redirect::route('user.index');
    }

    public function logout()
    {
        Session::forget('username');
        return Redirect::route('user.index');
    }

    public function getRefresh(Request $request)
    {
        $code = $request->input('code');

        $spotify = new SpotifyService();
        $refresh = $spotify->getRefresh($code);

        if (!$refresh)
        {
            return;
        }

        print_r($spotify->currentlyPlaying());

        $search = $spotify->searchTrack('Never gonna give');
        print_r($search);

        $spotify->addToQueue($search[0]['uri']);
        $spotify->skipTrack();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Sparty/User/CreateAccount');
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
            'username' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Session::forget('username');
        Session::push('username', $request->username);

        return Redirect::route('user.index');
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
