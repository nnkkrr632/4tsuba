<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('p@ssw0rd'),
            'role' => 'normal',
            'icon_name' => 'no_image.png'
        ];
    }
    /**
     * メールアドレスを指定する
     */
    public function setEmail(string $email)
    {
        return $this->state(fn () => [
            'email' => $email,
        ]);
    }

    /**
     * パスワードをデフォルトの「password」から変更する
     */
    public function setPassword(string $password)
    {
        return $this->state(fn () => [
            'password' => bcrypt($password),
        ]);
    }
    /**
     * roleをゲストにする
     */
    public function setRoleGuest()
    {
        return $this->state(fn () => [
            'role' => 'guest',
        ]);
    }
    /**
     * roleをスタッフにする
     */
    public function setRoleStaff()
    {
        return $this->state(fn () => [
            'role' => 'staff',
        ]);
    }
}
