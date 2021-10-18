<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Monitor;
use Illuminate\Support\Facades\Storage;

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
        $monitor = new Monitor();
        $converted_name = $monitor->convertNgWordsIfExist($edit_profile_request->name);
        User::find(Auth::id())->update(['name' => $converted_name,]);

        if ($edit_profile_request->file('icon')) {
            //新しいアイコンを保存
            $uploaded_icon = $edit_profile_request->file('icon');
            $uploaded_icon->store('public/icons');
            //古いアイコンを削除(ただし初期アイコンとゲスト用アイコン以外)
            $my_icon = Auth::user()->icon_name;
            if (!preg_match('/no_image|guest_user/', $my_icon)) {
                Storage::disk('public')->delete('icons/' . $my_icon);
            }
            //DB更新
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
            //古いアイコンを削除(ただしゲスト用アイコン以外)
            $my_icon = Auth::user()->icon_name;
            if (!preg_match('/guest_user/', $my_icon)) {
                Storage::delete('public/icons/' . $my_icon);
            }

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
            //アイコンを削除(ただし初期アイコンとゲスト用アイコン以外)
            $my_icon = Auth::user()->icon_name;
            if (!preg_match('/no_image|guest_user/', $my_icon)) {
                Storage::disk('public')->delete('icons/' . $my_icon);
            }
            $user->delete();
        } else {
            return 'bad_password';
        }
    }
}
