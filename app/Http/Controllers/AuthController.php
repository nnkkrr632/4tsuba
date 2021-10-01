<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
//フォームリクエスト
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
