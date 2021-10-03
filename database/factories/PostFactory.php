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
    private static $d_p_id = [1, 1, 1, 1];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // $post = new Post();
        // $thread_id = Thread::all()->random()->id;
        // $inserting_displayed_post_id = $post->returnMaxDisplayedPostId($thread_id) + 1;

        return [
            'user_id' => User::all()->random()->id,
            'thread_id' => Thread::all()->random()->id,
            'displayed_post_id' => self::$d_p_id[0]++,
            'body' => $this->faker->realText(30),
            'is_edited' => 0,
        ];
    }
}
