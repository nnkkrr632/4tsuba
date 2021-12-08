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
    private static $overview_fields = ['active_users_count', 'posts_count', 'likes_count'];

    //ログインしたユーザーIDをbitに登録
    public function storeAuthUserLoggedIn()
    {
        $today = new Carbon();
        $redis_key = self::KEY_PREFIX_ACTIVE_USERS . $today->toDateString();

        //返り値は既にbitに格納されている値 故に初回ログイン時のみ返り値は0
        $already_logged_in_today = Redis::setbit($redis_key, Auth::id(), 1);
        if (!$already_logged_in_today) {
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
     * デイリーアクティブユーザーの詳細 array($user_id => 1, $user_id => 0,  ...)
     *
     * @param string $date
     * @return array
     */
    public function returnDailyActiveUsersBitMap(string $date)
    {
        $redis_key_for_date = $this->returnRedisKeyForDailyActiveUsers($date);
        return $this->returnActiveUsersBitMap($redis_key_for_date);
    }

    public function returnMonthlyActiveUsersDetail(string $year_month)
    {
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
        return  (int)Redis::bitcount($redis_key);
    }

    /**
     * マンスリーアクティブユーザーの詳細 array($user_id => 0, user_id => 1,  ...)
     *
     * @param string $year_month
     * @return array
     */
    public function returnMonthlyActiveUsersBitMap(string $year_month)
    {
        //月の論理和bitの作成 & そのbitのkeyを取得
        $redis_key_for_month = $this->generateMonthBitMapAndReturnTheBitKey($year_month);
        return $this->returnActiveUsersBitMap($redis_key_for_month);
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
     * 【デイリー/マンスリー】redisキーを受け取り、そのキーに対するログインユーザーを返却
     * 
     * @param string $redis_key
     * @return array
     */
    public function returnActiveUsersInfo(string $redis_key)
    {
        $users = User::get();
        $users_info = [];
        foreach ($users as $user) {
            $zero_or_one = Redis::getbit($redis_key, $user->id);
            if ($zero_or_one) {
                $user_info = ['user_id' => $user->id, 'name' => $user->name, 'icon_name' => $user->icon_name];
                array_push($users_info, $user_info);
            }
        }
        return $users_info;
    }

    /**
     * 【マンスリー】年/月を受け取り、その月のセットを返却
     * [
     *  [ '2021-12-01' => ['user_id' => 1, 'active' => 0, 'icon_name' => 'xxx'], ['user_id' => ...],]
     *  [ '2021-12-02' => ['user_id' => 1, 'active' => 1, 'icon_name' => 'xxx'], ['user_id' => ...],]
     * ]
     * @param string $year_month
     * @return array
     */
    public function returnMonthlyActiveUsersSet(string $year_month)
    {
        //carbonは月の1日0時0分0秒
        $carbon = new Carbon($year_month);

        $monthly_users_set = [];
        foreach (range(1, $carbon->daysInMonth) as $each_day) {
            $suffix_day = sprintf("%02d", $each_day);
            //.envでredisのprefix(=4tsuba-)を定義しているため、key名に'4tsuba-'が不要
            $each_key = self::KEY_PREFIX_ACTIVE_USERS . $carbon->format("Y-m") . '-' . $suffix_day;
            $each_users_set = $this->returnActiveUsersInfo($each_key);
            array_push($monthly_users_set, $each_users_set);
        }
        return $monthly_users_set;
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

        $keys = [];
        foreach (range(1, $carbon->daysInMonth) as $each_day) {
            $suffix_day = sprintf("%02d", $each_day);
            //.envでredisのprefix(=4tsuba-)を定義しているため、key名に'4tsuba-'が不要
            $each_key = self::KEY_PREFIX_ACTIVE_USERS . $carbon->format("Y-m") . '-' . $suffix_day;
            array_push($keys, $each_key);
        }
        //第3引数に論理演算対象のkeysが格納された配列を指定しbitop実行
        Redis::bitop('or', $redis_key_for_monthly_active_users, $keys);

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
            $hash_key = self::KEY_PREFIX_OVERVIEW . $date_string;
            $active_users_key = self::KEY_PREFIX_ACTIVE_USERS . $date_string;

            //それぞれの日のレポートをredisから取得
            $each_day_overview = Redis::hgetall($hash_key);
            $each_day_overview['date'] = $date_string;

            $users_info = $this->returnActiveUsersInfo($active_users_key);
            $each_day_overview['users_info'] = $users_info;

            array_push($month_overview, $each_day_overview);
        }
        return $month_overview;
    }


    //ハッシュ作成・更新系
    /**
     * @param string $field
     * @return void
     */
    public function incrementHashForOverview(string $field)
    {
        $today = new Carbon('now');
        $hash_key = self::KEY_PREFIX_OVERVIEW . $today->toDateString();
        Redis::hincrby($hash_key, $field, 1);
    }

    /**
     * @param string $field
     * @return void
     */
    public function decrementHashForOverview(string $field)
    {
        $today = new Carbon('now');
        $hash_key = self::KEY_PREFIX_OVERVIEW . $today->toDateString();
        Redis::hincrby($hash_key, $field, -1);
    }
}
