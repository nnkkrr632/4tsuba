<?php

namespace Database\Factories;

use App\Models\Like;
use App\Models\User;
use App\Models\Post;

use Illuminate\Database\Eloquent\Factories\Factory;

class LikeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Like::class;
    private static $post_id = 1;
    private static $user_id = 1;

    //クラス変数$post_idはテーブルがtruncateされても増分はされたまま。
    //テスト時にこれを呼び出して$post_idをリセットする
    public static function initializePostId()
    {
        self::$post_id = 1;
    }


    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => self::$user_id,
            'post_id' => self::$post_id++,
        ];
    }
    /**
     * 対象ユーザーを指定する
     */
    public function setUserId(int $user_id)
    {
        return $this->state(fn () => [
            'user_id' => $user_id,
        ]);
    }
    /**
     * 対象ポストとユーザーを指定する
     */
    public function setUserIdAndPostId(int $user_id, int $post_id)
    {
        return $this->state(fn () => [
            'user_id' => $user_id,
            'post_id' => $post_id,
        ]);
    }
}
