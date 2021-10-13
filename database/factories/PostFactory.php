<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;
//使用するmodelをインポートする
use App\Models\Post;
use App\Models\User;
use App\Models\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;


class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;
    private static $user_id = 1;
    private static $thread_id = 1;
    private static $displayed_post_id = 1;


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => self::$user_id,
            'thread_id' => self::$thread_id,
            'displayed_post_id' => self::$displayed_post_id++,
            'body' => $this->faker->realText(30),
            'is_edited' => 0,
        ];
    }
    /**
     * スレッドを指定する
     */
    public function setThreadId(int $thread_id)
    {
        //displayed_post_id初期化してまた1から開始
        self::$displayed_post_id = 1;
        return $this->state(fn () => [
            'thread_id' => $thread_id,
        ]);
    }
    /**
     * ユーザーを指定する
     */
    public function setUserId(int $user_id)
    {
        return $this->state(fn () => [
            'user_id' => $user_id,
        ]);
    }
}
