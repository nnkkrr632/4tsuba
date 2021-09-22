<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class GuestAuthController extends Controller
{
    public function checkLoginOrNot()
    {
        return Auth::check();
    }

    public function guestLogin($user_id)
    {
        //ゲストユーザーIDを1,2,3に限定する
        if (!in_array((int)$user_id, [1, 2, 3], true)) {
            $user_id = 1;
        }

        //ゲストログインではメールアドレスの代わりにid(主キー)のみを使う
        //その場合、attemptに代わりloginUsingIdメソッドを使うとgood
        Auth::loginUsingId($user_id, $remember = true);
        return response()->json(['name' => Auth::user()->name], 200);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'ログアウト成功'], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'between:1,10'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'between:8,20'],
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        //user作成が成功したら、ログイン扱い
        $this->login($request);
    }
}
