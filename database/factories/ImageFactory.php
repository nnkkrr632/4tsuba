<?php

namespace Database\Factories;

use App\Models\Image;
use Illuminate\Database\Eloquent\Factories\Factory;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Image::class;
    private static $thread_id = 1;
    private static $post_id = 1;

    //クラス変数$displayed_post_idはテーブルがtruncateされても増分はされたまま。
    //テスト時にこれを呼び出してdisplayed_post_idをリセットする
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
        //factoryで実際のファイルも生成できちゃう
        $uploaded_image = UploadedFile::fake()->image('item.jpg');
        $path = $uploaded_image->store('public/images');

        return [
            'thread_id' => self::$thread_id,
            'post_id' => self::$post_id++,
            'image_name' => $uploaded_image->hashName(),
            'image_size' => $uploaded_image->getSize(),
        ];
    }
    /**
     * スレッドを指定する
     */
    public function setThreadId(int $thread_id)
    {
        //post_id初期化してまた1から開始
        self::$post_id = 1;
        return $this->state(fn () => [
            'thread_id' => $thread_id,
        ]);
    }
    /**
     * ポストidを指定する
     */
    public function setPostId(int $post_id)
    {
        return $this->state(fn () => [
            'post_id' => $post_id,
        ]);
    }
}
