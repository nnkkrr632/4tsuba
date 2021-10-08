<?php

namespace Database\Factories;

use App\Models\MuteUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class MuteUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MuteUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'muting_user_id' => User::factory()->create()->id,
            'user_id' => 10,
        ];
    }
}
