<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
//Carbonを使用する
use Carbon\Carbon;

class RedisDashboardController extends Controller
{
    public function returnActiveUserCount(Request $request)
    {
        $date = new Carbon('now');

        //日別


        //date_stringは柔軟。2021/10/10でも2021-10-10でもフォーマットできる(_は無理)
        $date = new Carbon($request->term);
        $active_user_count = Redis::bitcount(self::KEY_PREFIX_LOGIN . $date->toDateString);
        return $active_user_count;

        /**
         * @return mixed
         */
    }
}
