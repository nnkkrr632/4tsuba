<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ThreadPolicy
{
    use HandlesAuthorization;

    // /**
    //  * Determine whether the user can update the model.
    //  *
    //  * @param  \App\Models\User  $user
    //  * @param  \App\Models\Thread  $thread
    //  * @return \Illuminate\Auth\Access\Response|bool
    //  */
    // public function update(User $user, Thread $thread)
    // {
    //     return $user->role === 'staff'
    //         ? Response::allow()
    //         : Response::deny('bad_user');
    // }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Thread  $thread
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Thread $thread)
    {
        return $user->role === 'staff'
            ? Response::allow()
            : Response::deny('bad_user');
    }
}
