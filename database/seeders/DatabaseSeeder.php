<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\TasksTableSeeder;
//ファクトリーを使うためにインポート (modelのfactory()メソッドを使う)
use App\Models\Thread;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(10)->create();
        $this->call(TasksTableSeeder::class);
        Thread::factory()->count(10)->create();
    }
}
