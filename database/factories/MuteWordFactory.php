<?php

namespace Database\Factories;

use App\Models\MuteWord;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class MuteWordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MuteWord::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory()->create()->id,
            'mute_word' => $this->faker->realText(10),
        ];
    }
}
