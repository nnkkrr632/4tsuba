<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User;
//フォームリクエスト
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\EditAccountRequest;
use App\Http\Requests\DestroyAccountRequest;


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
        if (Auth::check()) {
            return User::find(Auth::id())->makeVisible(['email']);
        } else {
            return null;
        }
    }

    public function editAccount(EditAccountRequest $edit_account_request)
    {
        $user = User::findOrFail(Auth::id());
        //ユーザーがゲストでないことを確認
        $this->authorize('checkUserIsNotGuest', $user);

        if ($user->checkPassword($edit_account_request->current_password)) {

            $user->update([
                'email' => $edit_account_request->email,
                'password' => bcrypt($edit_account_request->password)
            ]);
        } else {
            return 'bad_password';
        }
    }

    public function login(LoginRequest $login_request)
    {
        $credentials = $login_request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return response()->json(['name' => Auth::user()->name], 200);
        }
        //↓のHTTP422エラーが出るときはフォームリクエストのバリデーションを突破している
        throw ValidationException::withMessages([
            'email_password' => 'メールアドレスもしくはパスワードが正しくありません。'
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'ログアウト成功'], 200);
    }

    //登録
    public function register(RegisterRequest $register_request)
    {
        //password_confirmはフォームリクエストのみで使ってコントローラーでは使わない
        User::create([
            'name' => $register_request->name,
            'email' => $register_request->email,
            'password' => bcrypt($register_request->password)
        ]);

        //user作成が成功したら、ログイン扱い
        $login_request = new LoginRequest();
        $login_request->merge([
            'email' => $register_request->email,
            'password' => $register_request->password,
        ]);
        $this->login($login_request);
    }

    public function destroy(DestroyAccountRequest $destroy_account_request)
    {
        $user = User::findOrFail(Auth::id());
        //ユーザーがゲストでないことを確認
        $this->authorize('checkUserIsNotGuest', $user);

        if ($user->checkPassword($destroy_account_request->password)) {
            $user->delete();
        } else {
            return 'bad_password';
        }
    }
}
