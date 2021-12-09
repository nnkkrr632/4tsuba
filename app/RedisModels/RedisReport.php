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
    private const KEY_PREFIX_LIKES_COUNT = 'likes-count-';
    private static $overview_fields = ['active_users_count', 'posts_count', 'likes_count'];

    //【ストア系】

    //アクティブユーザーをbitにストア
    public function storeAuthUserLoggedIn()
    {
        $today = new Carbon();
        $redis_key = self::KEY_PREFIX_ACTIVE_USERS . $today->toDateString();

        //返り値は既にbitに格納されている値 故に初回ログイン時のみ返り値は0
        Redis::setbit($redis_key, Auth::id(), 1);
    }

    //書込 ユーザーidと書込み数を zset(sorted set)にストア
    public function storePostsCountAndPostedUserId(int $user_id)
    {
        $today = new Carbon();
        $redis_key = self::KEY_PREFIX_POSTS_COUNT . $today->toDateString();
        // zincrbyはキーが存在しない場合でもzsetを作成し、メンバーとスコアを生成してくれる
        Redis::zincrby($redis_key, 1, $user_id);
    }

    //いいね ユーザーidといいね数を zset(sorted set)にストア
    public function storeLikesCountAndLikedUserId(int $user_id)
    {
        $today = new Carbon();
        $redis_key = self::KEY_PREFIX_LIKES_COUNT . $today->toDateString();
        // zincrbyはキーが存在しない場合でもzsetを作成し、メンバーとスコアを生成してくれる
        Redis::zincrby($redis_key, 1, $user_id);
    }


    //【デストロイ系】↑のストア系の取り消し

    //書込 ユーザーidと書込み数を zset(sorted set)にストア
    public function destroyPostsCountAndPostedUserId(int $user_id)
    {
        $today = new Carbon();
        $redis_key = self::KEY_PREFIX_POSTS_COUNT . $today->toDateString();
        Redis::zincrby($redis_key, -1, $user_id);
    }

    //いいね ユーザーidといいね数を zset(sorted set)にストア
    public function destroyLikesCountAndLikedUserId(int $user_id)
    {
        $today = new Carbon();
        $redis_key = self::KEY_PREFIX_LIKES_COUNT . $today->toDateString();
        Redis::zincrby($redis_key, -1, $user_id);
    }

    //Overview Vueから呼ばれるレポート

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
            $active_users_key = self::KEY_PREFIX_ACTIVE_USERS . $date_string;
            $posts_count_key = self::KEY_PREFIX_POSTS_COUNT . $date_string;
            $likes_count_key = self::KEY_PREFIX_LIKES_COUNT . $date_string;

            //日単位のoverviewを用意 これに↓の3情報を詰めていく
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

            //【いいねカウント情報】
            $likes_count_info = $this->returnLikesCountInfo($users, $likes_count_key);
            $each_day_overview['likes_count_info'] = $likes_count_info;
            //ポストカウント日合計を計算 because $likes_count_infoのlengthを取得すると、
            //一人あたりのlike回数にアクセスしないため、ポストした人の人数を取得することになってしまう
            $daily_total_likes_count = 0;
            foreach ($likes_count_info as $each_user_likes_count_info) {
                $daily_total_likes_count += $each_user_likes_count_info['likes_count'];
            }
            $each_day_overview['daily_total_likes_count'] = $daily_total_likes_count;

            //日単位のoverviewに3情報を詰め終わったら、月単位のoverviewに日単位のoverviewを詰める
            array_push($month_overview, $each_day_overview);
        }
        return $month_overview;
    }

    //【アクティブユーザー】Overviewに呼び出される
    private function returnActiveUsersInfo(Collection $users, string $redis_key)
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

    //【書込】Overviewに呼び出される
    private function returnPostsCountInfo(Collection $users, string $redis_key)
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

    //【いいね】Overviewに呼び出される
    private function returnLikesCountInfo(Collection $users, string $redis_key)
    {
        $users_and_likes_count = Redis::zrevrange($redis_key, 0, -1, 'withscores');

        $likes_count_info = [];
        foreach ($users_and_likes_count as $user_id => $likes_count) {
            $each_like_count_info = [
                'user_id' => $user_id, 'likes_count' => $likes_count,
                'name' => $users->where('id', $user_id)[(int)$user_id - 1]->name,
                'icon_name' => $users->where('id', $user_id)[(int)$user_id - 1]->icon_name,
            ];
            array_push($likes_count_info, $each_like_count_info);
        }
        return $likes_count_info;
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
}
