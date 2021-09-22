<?php

namespace App\Policies;

use App\Models\MuteWord;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MuteWordPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MuteWord  $muteWord
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, MuteWord $mute_word)
    {
        return $user->id == $mute_word->user_id;
    }
}
