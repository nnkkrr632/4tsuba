<?php

namespace App\RedisModels;

//redisを使用する
use Illuminate\Support\Facades\Redis;
//authを使用する
use Illuminate\Support\Facades\Auth;
//DateTimeを使用する
use DateTime;


class RedisPost
{

    /**
     * @param array $search_word_list
     */
    public function store(array $search_word_list)
    {
        $search_word_string = implode(' ', $search_word_list);
        $unix_datetime = new DateTime();
        $unix_datetime_int = (int)($unix_datetime->format('U'));
        //redisのzsetに登録
        Redis::command('zadd', ['search_history', $unix_datetime_int, $search_word_string]);
    }
    /**
     * @return array $search_history
     */
    public function show()
    {
        //zsetから直近5つを取得
        $search_history = Redis::zrevrange('search_history', 0, 4);
        return $search_history;
    }
}
