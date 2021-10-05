<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Thread;
use App\Models\User;
use App\Models\Post;


class ThreadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $post = new Post();

        DB::table('threads')->insert([
            [
                'user_id' => User::all()->random()->id,
                'title' => '今日食べたフルーツスレッド',
            ],
            [
                'user_id' => User::all()->random()->id,
                'title' => '明日やりたいことスレッド',
            ],
            [
                'user_id' => User::all()->random()->id,
                'title' => 'らくがき帳スレッド',
            ],
            [
                'user_id' => User::all()->random()->id,
                'title' => '溜池山王のうまい店スレッド',
            ],
        ]);
    }
}
