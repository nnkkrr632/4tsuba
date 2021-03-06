<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'name' => 'ゲストユーザー1',
                'email' => 'g_u_1@4tsuba.site',
                'password' => bcrypt('guestUser1'),
                'icon_name' => 'guest_user_1.png',
                'role' => 'guest',
            ],
            [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'name' => 'ゲストユーザー2',
                'email' => 'g_u_2@4tsuba.site',
                'password' => bcrypt('guestUser2'),
                'icon_name' => 'guest_user_2.png',
                'role' => 'guest',
            ],
            [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'name' => 'ゲストユーザー3',
                'email' => 'g_u_3@4tsuba.site',
                'password' => bcrypt('guestUser3'),
                'icon_name' => 'guest_user_3.png',
                'role' => 'guest',
            ],
            [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'name' => 'よっちゃん',
                'email' => 'yocchan@4tsuba.site',
                'password' => bcrypt('laterChanged1'),
                'icon_name' => 'no_image.png',
                'role' => 'staff',
            ],
            [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'name' => 'よっしー',
                'email' => 'yosshi@4tsuba.site',
                'password' => bcrypt('laterChanged1'),
                'icon_name' => 'no_image.png',
                'role' => 'normal',
            ],
        ]);
    }
}
