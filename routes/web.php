<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
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

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::resource('/user', UserController::class);
Route::resource('/room', RoomController::class);
Route::get('/search', [RoomController::class, 'search'])->name('search');
Route::get('/createRoom', [RoomController::class, 'create'])->name('createRoom');
Route::get('/code', [UserController::class, 'getRefresh'])->name('code');
Route::get('/connection', [UserController::class, 'connection'])->name('connection');
Route::get('/logout', [UserController::class, 'logout'])->name('logout');
Route::post('/checkLogin', [UserController::class, 'checkLogin'])->name('checkLogin');
Route::post('/delete', [RoomController::class, 'delete'])->name('delete');
Route::get('/createAccount', [UserController::class, 'create'])->name('createAccount');
Route::post('/addMusic', [RoomController::class, 'addMusic'])->name('addMusic');
Route::post('/vote', [RoomController::class, 'vote'])->name('vote');
Route::get('/admin', [AdminController::class, 'index'])->name('admin');
Route::post('/delete-track', [AdminController::class, 'deleteTrack'])->name('deleteTrack');
Route::post('/ban-guest', [AdminController::class, 'banGuest'])->name('banGuest');
Route::post('/add-admin', [AdminController::class, 'addAdmin'])->name('addAdmin');
Route::post('/lock-room', [AdminController::class, 'lockRoom'])->name('lockRoom');
Route::post('/play-playlist', [AdminController::class, 'playPlaylist'])->name('playPlaylist');
Route::post('/delete-room', [AdminController::class, 'deleteRoom'])->name('deleteRoom');
Route::post('/checkRoom', [RoomController::class, 'checkRoom'])->name('checkRoom');
Route::get('/joinRoom', [RoomController::class, 'joinRoom'])->name('joinRoom');
Route::get('/toMyRoom', [UserController::class, 'toMyRoom'])->name('toMyRoom');
