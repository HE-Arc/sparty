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
        $username = Session::get("username");
        $spotifyUsername = "Not connected yet!";

        if($username != null)
        {
            $refresh = User::where('username', '=', $username)->first()->refresh;

            if($refresh != null)
            {
                $spotify = new SpotifyService($refresh);
                $spotifyUsername = $spotify->getUserName();
            }

            if(!$spotifyUsername)
            {
                $spotifyUsername = "Not connected yet!";
            }

            $username = $username;
            return inertia('Sparty/User/Index', [
                'username' => $username,
                'spotifyUsername' => $spotifyUsername,
                'status' => Session::get('status'),
            ]);
        }

        return Inertia::render('Sparty/User/Login', [
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
            Session::put('username', $username);
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

    public function connection()
    {
        $spotify = new SpotifyService();
        return Inertia::location($spotify->redirect());
    }

    public function getRefresh(Request $request)
    {
        if (Session::has('username'))
        {
            $code = $request->input('code');

            $spotify = new SpotifyService();
            $refresh = $spotify->getRefresh($code);

            if (!$refresh)
            {
                return;
            }

            //faire une verification de username dans session
            $currentUser = User::where('username', '=', Session::get('username'))->first();
            $currentUser->refresh = $refresh;
            $currentUser->save();
        }

        return Redirect::route('user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Sparty/User/CreateAccount', [
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
            'username' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if(User::where('username', '=', $request->username)->exists())
        {
            $request->session()->flash('status', "Username is already in use!");
            return Redirect::route('user.create');
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Session::forget('username');
        Session::put('username', $request->username);

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
