<?php

namespace App\RedisModels;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
//Carbonを使用する
use Carbon\Carbon;


class RedisReport
{
    private const KEY_PREFIX_OVERVIEW = 'report-overview-';
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

    /**
     * @param string $year_month
     * @return array
     */
    public function returnOverview(string $year_month)
    {
        $date = new Carbon($year_month);

        $month_overview = [];
        foreach (range(1, $date->daysInMonth) as $each_day) {

            $suffix_day = sprintf("%02d", $each_day);
            $date_string = $date->format("Y-m") . '-' . $suffix_day;

            //それぞれの日のレポートをredisから取得
            $hash_key = self::KEY_PREFIX_OVERVIEW . $date_string;
            $each_day_overview = Redis::hgetall($hash_key);
            $each_day_overview['date'] = $date_string;

            array_push($month_overview, $each_day_overview);
        }
        return $month_overview;
    }
    /**
     * @param string $field
     * @return void
     */
    public function incrementHash(string $field)
    {
        $date = new Carbon('now');
        $hash_key = self::KEY_PREFIX_OVERVIEW . $date->toDateString();
        Redis::hincrby($hash_key, $field, 1);
    }
    /**
     * @param string $field
     * @return void
     */
    public function decrementHash(string $field)
    {
        $date = new Carbon('now');
        $hash_key = self::KEY_PREFIX_OVERVIEW . $date->toDateString();
        Redis::hincrby($hash_key, $field, -1);
    }
}
