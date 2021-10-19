<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;


    /**
     * @param  \App\Models\User  $model
     */
    public function checkUserIsGuest(User $user)
    {
        return $user->role === 'guest';
    }
    /**
     * @param  \App\Models\User  $model
     */
    public function checkUserIsNotGuest(User $user)
    {
        return $user->role !== 'guest';
    }
    // /**
    //  * @param  \App\Models\User  $model
    //  */
    // public function checkUserIsStaff(User $user)
    // {
    //     return $user->role === 'staff';
    // }
    // /**
    //  * @param  \App\Models\User  $model
    //  */
    // public function checkUserIsNotStaff(User $user)
    // {
    //     return $user->role !== 'staff';
    // }
}
