<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RedisModels\RedisPost;

class RedisPostController extends Controller
{

    public function store(array $search_word_list)
    {
        $redis_post = new RedisPost();
        $redis_post->store($search_word_list);
    }

    public function show()
    {
        $redis_post = new RedisPost();
        return $redis_post->show();
    }
}
