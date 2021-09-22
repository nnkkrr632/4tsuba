<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class AuthController extends Controller
{

    public function checkLoginOrNot()
    {
        return Auth::check();
    }

    public function returnMyId()
    {
        if (Auth::id()) {
            return Auth::id();
        } else {
            return null;
        }
    }

    public function returnMyInfo()
    {
        if (Auth::id()) {
            return User::find(Auth::id())->makeVisible(['email']);
        } else {
            return null;
        }
    }

    public function editAccount(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        //ユーザーがゲストでないことを確認
        $this->authorize('checkUserIsNotGuest', $user);

        if ($user->checkPassword($request->current_password)) {
            // $request->validate([
            //     'email' => ['required', 'email', 'unique:users'],
            //     'password' => ['required', 'between:8,20'],
            // ]);


            $user->update([
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
        } else {
            return 'bad_password';
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return response()->json(['name' => Auth::user()->name], 200);
        }

        throw ValidationException::withMessages([
            'email' => '認証情報が正しくありません'
        ]);
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

    public function destroy(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        //ユーザーがゲストでないことを確認
        $this->authorize('checkUserIsNotGuest', $user);

        if ($user->checkPassword($request->password)) {
            $user->delete();
        } else {
            return 'bad_password';
        }
    }
}
