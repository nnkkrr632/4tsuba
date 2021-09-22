<?php

namespace Database\Factories;

//使用するmodelをインポートする
use App\Models\Thread;
use App\Models\User;
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
            'title' => $this->faker->realText(60),
            'post_count' => $this->faker->numberBetween(10, 1000),
            'like_count' => $this->faker->numberBetween(10, 1000)
        ];
    }
}
