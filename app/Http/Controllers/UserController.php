<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function returnUserInfo(Request $request)
    {
        $user_id = $request->user_id;
        return User::where('id', $user_id)
            ->withCount([
                'posts',
                'likes',
                'mute_users AS is_login_user_mute' => function ($query) use ($user_id) {
                    $query->where('muting_user_id', Auth::id())->where('user_id', $user_id);
                },
            ])->get();
    }

    public function exists($user_id)
    {
        //文字列をURLに入力されたら404送り
        if (preg_match('/\D/', $user_id)) {
            return 0;
        } else {
            $converted_user_id = (int)$user_id;
            return User::where('id', $converted_user_id)->count();
        }
    }
}
