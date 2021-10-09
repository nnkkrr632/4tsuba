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
            'post_count' => 0,
            'like_count' => 0,
        ];
    }
    /**
     * 対象ユーザーを指定する
     */
    public function setPostCountAndLikeCount(int $thread_id)
    {
        $post_count = Post::where('thread_id', $thread_id)->count();
        $like_count = Like::whereIn('post_id', function ($query) use ($thread_id) {
            $query->select('id')->from('posts')->where('thread_id', $thread_id);
        })->count();
        return $this->state(fn () => [
            'post_count' => $post_count,
            'like_count' => $like_count,
        ]);
    }
}
