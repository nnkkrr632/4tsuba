<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\CookieAuthenticationController;

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
//一般
Route::post('/register', [CookieAuthenticationController::class, 'register'])->name('register');
Route::post('/login', [CookieAuthenticationController::class, 'login'])->name('login');
Route::post('/logout', [CookieAuthenticationController::class, 'logout'])->name('logout');

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
