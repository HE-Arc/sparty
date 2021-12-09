<?php

use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource("/user", UserController::class);
Route::resource('/room', RoomController::class);
Route::get('/search', [RoomController::class, 'search'])->name('search');
Route::get('/createRoom', [RoomController::class, 'create'])->name('createRoom');
Route::get('/code', [UserController::class, 'getRefresh'])->name('code');
Route::get('/logout', [UserController::class, 'logout'])->name('logout');
Route::post('/checkLogin', [UserController::class, 'checkLogin'])->name('checkLogin');
Route::get('/createAccount', [UserController::class, 'create'])->name('createAccount');
Route::post('/test', [RoomController::class, 'test'])->name('test');
