<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    /**
     * Display the homepage
     *
     * @return Response the view
     */
    public function index()
    {
        return inertia('Sparty/Home/Index', [
            'status' => Session::get('status'),
            'username' => Session::get('username'),
            'room_id' => Session::get('room_id')
        ]);
    }
}
