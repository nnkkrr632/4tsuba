<?php

namespace App\Policies;

use App\Models\MuteUser;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MuteUserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MuteUser  $muteUser
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, MuteUser $mute_user)
    {
        return $user->id == $mute_user->muting_user_id;
    }
}
