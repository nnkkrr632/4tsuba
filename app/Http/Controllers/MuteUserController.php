<?php

namespace App\Http\Controllers;

use App\Models\MuteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
//フォームリクエスト
use App\Http\Requests\StoreMuteUserRequest;
use App\Http\Requests\DestroyMuteUserRequest;


class MuteUserController extends Controller
{
    public function index()
    {
        return MuteUser::where('muting_user_id', Auth::id())->orderBy('id', 'desc')
            ->pluck('user_id')->toArray();
    }

    public function store(StoreMuteUserRequest $store_mute_user_request)
    {
        MuteUser::create([
            'muting_user_id' => Auth::id(),
            'user_id' => $store_mute_user_request->user_id,
        ]);
    }

    public function destroy(DestroyMuteUserRequest $destroy_mute_user_request)
    {
        $target_mute_user = MuteUser::where('muting_user_id', Auth::id())
            ->where('user_id', $destroy_mute_user_request->user_id)->first();

        $this->authorize('delete', $target_mute_user);
        $target_mute_user->delete();
    }
}
