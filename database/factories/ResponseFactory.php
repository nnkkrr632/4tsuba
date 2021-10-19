<?php

namespace Database\Factories;

use App\Models\Response;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Response::class;
    private static $thread_id = 1;
    private static $origin_d_post_id = 1;

    //クラス変数$origin_d_post_idはテーブルがtruncateされても増分はされたまま。
    //テスト時にこれを呼び出して$origin_d_post_idをリセットする
    public static function initializeOriginDPostId()
    {
        self::$origin_d_post_id = 1;
    }

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'thread_id' => self::$thread_id,
            'origin_d_post_id' => self::$origin_d_post_id++,
            'dest_d_post_id' => 1,
        ];
    }
}
