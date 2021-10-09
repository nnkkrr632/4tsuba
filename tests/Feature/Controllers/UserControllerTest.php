<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class UserControllerTest extends TestCase
{
    //use RefreshDatabase;


    /** @test */
    public function ユーザー情報取得【returnUserInfo】()
    {
        //ユーザーをfactoryで作成
        $users = User::factory(2)->create();
        $acting_user = $users[0];
        $target_user = $users[1];
        $this->actingAs($acting_user);

        $threads = Thread::factory()->count(1)->create();
        $posts = Post::factory()->count(10)->setUserId($target_user->id)->create();

        $response = $this->json('GET', '/api/users/' . $target_user->id);
        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson(
                [
                    [
                        'icon_name' => 'no_image.png',
                        'id' => $target_user->id,
                        'is_login_user_mute' => 0,
                        'likes_count' => 0,
                        'name' => $target_user->name,
                        'posts_count' => count($posts),
                        'role' => 'normal',
                    ],
                ]
            );
    }
    /** @test */
    public function ページ遷移前ユーザー存在確認成功【exists】()
    {
        //ユーザーをfactoryで作成
        $users = User::factory(2)->create();
        $acting_user = $users[0];
        $exists_user = $users[1];
        $this->actingAs($acting_user);

        $response = $this->json('GET', '/api/exists/users/' . $exists_user->id);
        $response->assertStatus(200);
        $this->assertSame(1, $response->original);
    }
    /** @test */
    public function ページ遷移前ユーザー存在確認失敗【exists】()
    {
        //ユーザーをfactoryで作成
        $users = User::factory(1)->create();
        $acting_user = $users[0];
        $this->actingAs($acting_user);

        $response = $this->json('GET', '/api/exists/users/123');
        $response->assertStatus(200);
        $this->assertSame(0, $response->original);
    }

    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
