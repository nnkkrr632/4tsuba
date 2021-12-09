<?php

namespace App\RedisModels;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;


class RedisReport
{
    private const KEY_PREFIX_OVERVIEW = 'report-overview-';
    private const KEY_PREFIX_ACTIVE_USERS = 'active-users-';
    private const KEY_PREFIX_POSTS_COUNT = 'posts-count-';
    private static $overview_fields = ['active_users_count', 'posts_count', 'likes_count'];

    //【ストア系】

    //アクティブユーザーをbitにストア
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

    //書込 ユーザーidと書込み数を zset(sorted set)にストア
    public function storePostsCountAndPostedUserId(int $user_id)
    {
        $today = new Carbon();
        $redis_key = self::KEY_PREFIX_POSTS_COUNT . $today->toDateString();
        // zincrbyはキーが存在しない場合でもzsetを作成し、メンバーとスコアを生成してくれる
        Redis::zincrby($redis_key, 1, $user_id);
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
     * Overviewに呼び出される
     * redisキーを受け取り、そのキーに対するログインユーザーを返却
     * 
     * @param string $redis_key
     * @return array
     */
    public function returnActiveUsersInfo(Collection $users, string $redis_key)
    {
        $active_users_info = [];
        foreach ($users as $user) {
            $zero_or_one = Redis::getbit($redis_key, $user->id);
            if ($zero_or_one) {
                $active_user_info =
                    ['user_id' => $user->id, 'name' => $user->name, 'icon_name' => $user->icon_name];
                array_push($active_users_info, $active_user_info);
            }
        }
        return $active_users_info;
    }

    //Overviewに呼び出される
    public function returnPostsCountInfo(Collection $users, string $redis_key)
    {
        $users_and_posts_count = Redis::zrevrange($redis_key, 0, -1, 'withscores');

        $posts_count_info = [];
        foreach ($users_and_posts_count as $user_id => $posts_count) {
            $each_post_count_info = [
                'user_id' => $user_id, 'posts_count' => $posts_count,
                'name' => $users->where('id', $user_id)[(int)$user_id - 1]->name,
                'icon_name' => $users->where('id', $user_id)[(int)$user_id - 1]->icon_name,
            ];
            array_push($posts_count_info, $each_post_count_info);
        }
        return $posts_count_info;
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
        $users = User::get();
        $date = new Carbon($year_month);

        $month_overview = [];
        foreach (range(1, $date->daysInMonth) as $each_day) {

            //redisキーの生成
            $suffix_day = sprintf("%02d", $each_day);
            $date_string = $date->format("Y-m") . '-' . $suffix_day;
            $hash_key = self::KEY_PREFIX_OVERVIEW . $date_string;
            $active_users_key = self::KEY_PREFIX_ACTIVE_USERS . $date_string;
            $posts_count_key = self::KEY_PREFIX_POSTS_COUNT . $date_string;

            //【ハッシュ】それぞれの日のレポートをredisから取得
            $each_day_overview = Redis::hgetall($hash_key);
            $each_day_overview['date'] = $date_string;

            //【アクティブユーザー情報】
            $active_users_info = $this->returnActiveUsersInfo($users, $active_users_key);
            $each_day_overview['active_users_info'] = $active_users_info;

            //【ポストカウント情報】
            $posts_count_info = $this->returnPostsCountInfo($users, $posts_count_key);
            $each_day_overview['posts_count_info'] = $posts_count_info;
            //ポストカウント日合計を計算 because $posts_count_infoのlengthを取得すると、
            //一人あたりのpost回数にアクセスしないため、ポストした人の人数を取得することになってしまう
            $daily_total_posts_count = 0;
            foreach ($posts_count_info as $each_user_posts_count_info) {
                $daily_total_posts_count += $each_user_posts_count_info['posts_count'];
            }
            $each_day_overview['daily_total_posts_count'] = $daily_total_posts_count;

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
