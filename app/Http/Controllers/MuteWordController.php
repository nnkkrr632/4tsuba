<?php

namespace App\Http\Controllers;

use App\Models\MuteWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
//フォームリクエスト
use App\Http\Requests\StoreMuteWordRequest;
use App\Http\Requests\DestroyMuteWordRequest;

class MuteWordController extends Controller
{
    public function index()
    {
        return MuteWord::where('user_id', Auth::id())->orderBy('id', 'desc')->get();
    }

    public function store(StoreMuteWordRequest $store_mute_word_request)
    {
        MuteWord::create([
            'user_id' => Auth::id(),
            'mute_word' => $store_mute_word_request->mute_word,
        ]);
    }

    public function destroy(DestroyMuteWordRequest $destroy_mute_word_request)
    {
        $target_mute_word = MuteWord::where('id', $destroy_mute_word_request->id)->first();
        $this->authorize('delete', $target_mute_word);
        $target_mute_word->delete();
    }
}
