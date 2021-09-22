<?php

namespace Database\Factories;

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

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'thread_id' => Thread::factory(),
            'displayed_post_id' => $this->faker->numberBetween(10, 1000),
            'body' => $this->faker->realText(60),
            'is_edited' => '0',
        ];
    }
}
