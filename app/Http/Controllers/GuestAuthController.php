<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\GuestAuthRequest;

class GuestAuthController extends Controller
{
    public function guestLogin(GuestAuthRequest $guest_auth_request)
    {
        //ゲストログインではメールアドレスの代わりにid(主キー)のみを使う
        //その場合、attemptに代わりloginUsingIdメソッドを使うとgood

        // $user = User::findOrFail($guest_auth_request->user_id);
        //ユーザーのroleがゲストであることを確認(↓なぜかかできない)
        // $this->authorize('checkUserIsGuest', $user);
        //ログインしていないor自分への再度ログインなら許可
        if (!Auth::check() || Auth::id() === $guest_auth_request->user_id) {
            Auth::loginUsingId($guest_auth_request->user_id, $remember = true);
            return response()->json(['message' => 'guest_login_success', 'name' => Auth::user()->name], 200);
        } else {
            return response()->json(['message' => 'you_have_already_logged_in_another_account'], 200);
        }
    }
}
