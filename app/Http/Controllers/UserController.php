<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;



class UserController extends Controller
{
    public function returnUserInfo(Request $request)
    {
        //ミュートユーザー機能で誰もミューとしていないときnullが入ってくるからエスケープ
        if (!$request->user_id_list) {
            return null;
        } else {
            $user_id_list = $request->user_id_list;
            //ユーザー詳細ページとミュートユーザー一覧ページでこのメソッドを共有している。
            //前者では必ず1人に絞られるため$user_id_list[0]とすることでログインユーザーがその人をミューとしているか判定している
            return User::whereIn('id', $user_id_list)
                ->withCount([
                    'posts',
                    'likes',
                    'mute_users AS is_login_user_mute' => function ($query) use ($user_id_list) {
                        $query->where('muting_user_id', Auth::id())->where('user_id', $user_id_list[0]);
                    },
                ])->get();
        }
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

    public function editProfile(Request $request)
    {
        User::find(Auth::id())->update(['name' => $request->name,]);

        if ($request->file('icon')) {
            $uploaded_icon = $request->file('icon');
            $uploaded_icon->store('public/icons');
            User::find(Auth::id())->update([
                'icon_name' => $uploaded_icon->hashName(),
                'icon_size' => $uploaded_icon->getSize(),
            ]);
        }
    }

    public function resetGuestProfile()
    {
        $guest_user = User::find(Auth::id());
        if ($guest_user['role'] === 'guest') {
            User::find(Auth::id())->update([
                'name' => 'ゲストユーザー' . Auth::id(),
                'icon_name' => 'guest_user_' . Auth::id() . '.png',
            ]);
        }
    }
}
