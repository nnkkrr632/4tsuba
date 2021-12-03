<?php

namespace App\RedisModels;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
//Carbonを使用する
use Carbon\Carbon;


class RedisDashboard
{
    private const KEY_PREFIX_LOGIN = 'login-';
    private const LOGIN = 1;

    public function storeLogin()
    {
        $date = new Carbon();
        $redis_key = self::KEY_PREFIX_LOGIN . $date->toDateString();

        Redis::setbit($redis_key, Auth::id(), self::LOGIN);
    }
    /**
     * @param string $date_string
     * @return array $search_history
     */
    public function returnActiveUserCountInDay(string $date_string)
    {
        //date_stringは柔軟。2021/10/10でも2021-10-10でもフォーマットできる(_は無理)
        $date = new Carbon($date_string);
        $active_user_count = Redis::bitcount(self::KEY_PREFIX_LOGIN . $date->toDateString);
        return $active_user_count;
    }

    public function test()
    {
        return self::KEY_PREFIX_LOGIN . 'aaa' . self::LOGIN;
    }
}
