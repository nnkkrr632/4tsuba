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
    private static $user_id = 1;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'muting_user_id' => User::factory()->create()->id,
            'user_id' => self::$user_id++,
        ];
    }
    /**
     * ミューティングユーザーidを指定する
     */
    public function setMutingUserId(int $user_id)
    {
        return $this->state(fn () => [
            'muting_user_id' => $user_id,
        ]);
    }
}
