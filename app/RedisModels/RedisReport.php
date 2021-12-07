<?php

namespace App\RedisModels;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
//Carbonを使用する
use Carbon\Carbon;


class RedisReport
{
    private const KEY_PREFIX_OVERVIEW = 'report-overview-';
    private const KEY_PREFIX_ACTIVE_USERS = 'active-users-';
    private const LOGIN = 1;
    private static $overview_fields = ['active_users_count', 'posts_count', 'likes_count'];
    public $test = 'aaa';
    protected $test2 = 'bbb';
    private $test3 = 'ccc';
    public $array_t = ['ddd', 'eee', 'fff'];
    protected $array_t2 = ['ggg', 'hhh'];
    private $array_t3 = ['iii', 'jjj'];


    //ログインしたユーザーIDをbitに登録
    public function storeAuthUserLoggedIn()
    {
        $today = new Carbon();
        $redis_key = self::KEY_PREFIX_ACTIVE_USERS . $today->toDateString();

        //返り値は既にbitに格納されている値 故に初回ログイン時のみ返り値は0
        $first_login_today = Redis::setbit($redis_key, Auth::id(), self::LOGIN);
        if (!$first_login_today) {
            $this->incrementHashForOverview(self::$overview_fields[0]);
        }
    }

    //アクティブユーザー取得系

    /**
     * デイリーアクティブユーザー数取得
     *
     * @param string $date
     * @return int
     */
    public function returnDailyActiveUsersCount(string $date)
    {
        $redis_key = $this->returnRedisKeyForDailyActiveUsers($date);
        return (int)Redis::bitcount($redis_key);
    }

    /**
     * デイリーアクティブユーザーの詳細 array($user_id => 1, $user_id, 0,  ...)
     *
     * @param string $date
     * @return array
     */
    public function returnDailyActiveUsersBitMap(string $date)
    {
        $redis_key_for_date = $this->returnRedisKeyForDailyActiveUsers($date);
        $daily_active_users_bitmap = $this->returnActiveUsersBitMap($redis_key_for_date);
        return $daily_active_users_bitmap;
    }

    /**
     * マンスリーアクティブユーザー数取得
     *
     * @param string $year_month
     * @return int
     */
    public function returnMonthlyActiveUsersCount(string $year_month)
    {
        //月の論理和bitの作成 & そのbitのkeyを取得
        $redis_key = $this->generateMonthBitMapAndReturnTheBitKey($year_month);
        $monthly_active_users_count = (int)Redis::bitcount($redis_key);
        return $monthly_active_users_count;
    }

    /**
     * マンスリーアクティブユーザーの詳細 array($user_id => 1, $user_id, 0,  ...)
     *
     * @param string $year_month
     * @return array
     */
    public function returnMonthlyActiveUsersBitMap(string $year_month)
    {
        //月の論理和bitの作成 & そのbitのkeyを取得
        $redis_key_for_month = $this->generateMonthBitMapAndReturnTheBitKey($year_month);
        $monthly_active_users_bitmap = $this->returnActiveUsersBitMap($redis_key_for_month);
        return $monthly_active_users_bitmap;
    }



    //privateメソッド

    /**
     * 【デイリー】redisキー作成
     *
     * @param string $date
     * @return string
     */
    private function returnRedisKeyForDailyActiveUsers(string $date)
    {
        $carbon = new Carbon($date);
        $redis_key = self::KEY_PREFIX_ACTIVE_USERS . $carbon->toDateString();
        return $redis_key;
    }

    /**
     * 【デイリー/マンスリー】redisキーを受け取り、user_id => 0, user_id => 1, ... の連想配列を返却
     *
     * @param string $redis_key
     * @return array
     */
    private function returnActiveUsersBitMap(string $redis_key)
    {
        $user_id_list = range(1, User::count());
        $bit_list = [];
        foreach ($user_id_list as $user_id) {
            $zero_or_one = Redis::getbit($redis_key, $user_id);
            array_push($bit_list, $zero_or_one);
        }
        return array_combine($user_id_list, $bit_list);
    }

    /**
     * マンスリー集計用にbitop「or」でbitMap作成
     *
     * @param string $year_month
     * @return int
     */
    public function generateMonthBitMapAndReturnTheBitKey(string $year_month)
    {
        //carbonは月の1日0時0分0秒
        $carbon = new Carbon($year_month);
        $redis_key_for_monthly_active_users = self::KEY_PREFIX_ACTIVE_USERS . 'monthly-' . $carbon->format("Y-m");

        $dates = [];
        foreach (range(1, $carbon->daysInMonth) as $each_day) {
            $suffix_day = sprintf("%02d", $each_day);
            //.envでredisのprefix(=4tsuba-)を定義しているため、key名に'4tsuba-'が不要
            $each_key = self::KEY_PREFIX_ACTIVE_USERS . $carbon->format("Y-m") . '-' . $suffix_day;
            array_push($dates, $each_key);
        }
        //第3引数に論理演算対象のkeysが格納された配列を指定しbitop実行
        Redis::bitop('or', $redis_key_for_monthly_active_users, $dates);

        return $redis_key_for_monthly_active_users;
    }



    //Overview Hash関係

    /**
     * @param string $year_month
     * @return array
     */
    public function returnMonthlyOverview(string $year_month)
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
    public function incrementHashForOverview(string $field)
    {
        $date = new Carbon('now');
        $hash_key = self::KEY_PREFIX_OVERVIEW . $date->toDateString();
        Redis::hincrby($hash_key, $field, 1);
    }

    /**
     * @param string $field
     * @return void
     */
    public function decrementHashForOverview(string $field)
    {
        $date = new Carbon('now');
        $hash_key = self::KEY_PREFIX_OVERVIEW . $date->toDateString();
        Redis::hincrby($hash_key, $field, -1);
    }
}
