<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuestAuthController extends Controller
{
    public function checkLoginOrNot()
    {
        return Auth::check();
    }

    public function guestLogin($user_id)
    {
        //ゲストログインではメールアドレスの代わりにid(主キー)のみを使う
        //その場合、attemptに代わりloginUsingIdメソッドを使うとgood
        Auth::loginUsingId($user_id, $remember = true);
        return response()->json(['message' => 'login_success', 'name' => Auth::user()->name], 200);
    }
}
