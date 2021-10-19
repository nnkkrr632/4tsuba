<?php

namespace Database\Factories;

//使用するmodelをインポートする
use App\Models\Thread;
use App\Models\Post;
use App\Models\User;
use App\Models\Like;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThreadFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Thread::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->realText(20),
            'posts_count' => 0,
            'likes_count' => 0,
        ];
    }
    /**
     * ユーザーidを指定する
     */
    public function setUserId(int $user_id)
    {
        return $this->state(fn () => [
            'user_id' => $user_id,
        ]);
    }

    /**
     * ポスト数&いいね数を設定する
     */
    public function setPostCountAndLikeCount(int $posts_count, int $likes_count)
    {
        return $this->state(fn () => [
            'posts_count' => $posts_count,
            'likes_count' => $likes_count,
        ]);
    }
    /**
     * ポスト数&いいね数を実際の状態にあわせる
     */
    public function calculatePostCountAndLikeCount(int $thread_id)
    {
        $posts_count = Post::where('thread_id', $thread_id)->count();
        $likes_count = Like::whereIn('post_id', function ($query) use ($thread_id) {
            $query->select('id')->from('posts')->where('thread_id', $thread_id);
        })->count();
        return $this->state(fn () => [
            'posts_count' => $posts_count,
            'likes_count' => $likes_count,
        ]);
    }
}
