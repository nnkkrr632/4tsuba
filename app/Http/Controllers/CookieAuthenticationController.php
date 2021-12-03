<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\RedisModels\RedisReport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

//フォームリクエスト
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;

class CookieAuthenticationController extends Controller
{
    public function login(LoginRequest $login_request)
    {
        //ログインしていないor自分への再度ログインなら許可
        if (!Auth::check() || Auth::user()->email == $login_request->email) {
            $credentials = $login_request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                //redisに本ユーザーのログインを登録
                $redis_dashboard = new RedisReport();
                $redis_dashboard->storeLogin();

                return response()->json(['message' => 'login_success', 'name' => Auth::user()->name], 200);
            }
            //↓のHTTP422エラーが出るときはフォームリクエストのバリデーションを突破している
            throw ValidationException::withMessages([
                'email_password' => 'メールアドレスもしくはパスワードが正しくありません。'
            ]);
        } else {
            return response()->json(['message' => 'you_have_already_logged_in_another_account'], 200);
        }
    }

    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
            return response()->json(['message' => 'logout_success'], 200);
        } else {
            return response()->json(['message' => 'you_are_not_login'], 200);
        }
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
}
