<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
//ファクトリーを使うためにインポート (modelのfactory()メソッドを使う)
use App\Models\Thread;
use App\Models\User;
use App\Models\Post;
use App\Models\Like;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        //factoryから作成
        User::factory()->count(5)->create();
        //seederから作成
        $this->call(ThreadsSeeder::class);

        //Thread::factory()->count(10)->create();
        Post::factory()->count(20)->create();

        Like::factory()->count(100)->create();
    }
}
