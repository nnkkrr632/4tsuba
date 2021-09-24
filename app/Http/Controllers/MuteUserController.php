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
        $mute_user = new MuteUser();

        if ($mute_user->check_already_stored($request->user_id)) {
            return 'is_already_stored';
        } else if ($mute_user->check_mute_me($request->user_id)) {
            return "not_mute_me";
        } else {
            MuteUser::create([
                'muting_user_id' => Auth::id(),
                'user_id' => $request->user_id,
            ]);
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
