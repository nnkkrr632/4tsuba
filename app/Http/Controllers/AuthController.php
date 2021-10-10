<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
//フォームリクエスト
use App\Http\Requests\EditAccountRequest;
use App\Http\Requests\DestroyAccountRequest;
use App\Http\Requests\EditProfileRequest;


class AuthController extends Controller
{
    public function returnMyId()
    {
        return Auth::id();
    }

    public function returnMyInfo()
    {
        return User::find(Auth::id())->makeVisible(['email']);
    }

    public function editProfile(EditProfileRequest $edit_profile_request)
    {
        User::find(Auth::id())->update(['name' => $edit_profile_request->name,]);

        if ($edit_profile_request->file('icon')) {
            $uploaded_icon = $edit_profile_request->file('icon');
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
        } else {
            return 'for_guest_only.';
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
