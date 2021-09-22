<?php

namespace App\Http\Controllers;

use App\Models\MuteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class MuteUserController extends Controller
{
    public function index()
    {
        return MuteUser::where('muting_user_id', Auth::id())->orderBy('id', 'desc')
            ->pluck('user_id')->toArray();
    }

    public function store(Request $request)
    {
        //既に登録済みか否かをチェック
        $is_already_stored = MuteUser::where('muting_user_id', Auth::id())->where('user_id', $request->user_id)->count();
        if (!$is_already_stored) {
            MuteUser::create([
                'muting_user_id' => Auth::id(),
                'user_id' => $request->user_id,
            ]);
        } else {
            return "is_already_stored";
        }
    }

    public function destroy(Request $request)
    {
        $target_mute_user = MuteUser::where('muting_user_id', Auth::id())
            ->where('user_id', $request->user_id)->first();

        $this->authorize('delete', $target_mute_user);
        $target_mute_user->delete();
    }
}
