<?php

namespace App\Http\Controllers;

use App\Models\MuteWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MuteWordController extends Controller
{
    public function index()
    {
        return MuteWord::where('user_id', Auth::id())->orderBy('id', 'desc')->get();
    }

    public function store(Request $request)
    {

        //既に登録済みか否かをチェック
        $is_already_stored = MuteWord::where('user_id', Auth::id())->where('mute_word', $request->mute_word)->count();
        if (!$is_already_stored) {
            MuteWord::create([
                'user_id' => Auth::id(),
                'mute_word' => $request->mute_word,
            ]);
        } else {
            return "is_already_stored";
        }
    }

    public function destroy(Request $request)
    {
        $target_mute_word = MuteWord::where('user_id', Auth::id())->where('id', $request->id)->first();
        $this->authorize('delete', $target_mute_word);
        $target_mute_word->delete();
    }
}
