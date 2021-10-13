<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Response;
use Illuminate\Support\Facades\DB;

class ResponseControllerTest extends TestCase
{
    /**
     * @test
     */
    public function 表示ポスト個別遷移前返信先ポスト存在確認：存在する(): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $threads = Thread::factory()->count(1)->create();
        $posts = Post::factory()->count(5)->create();
        $responses = Response::factory()->count(5)->create();

        $url = '/api/exists/threads/1/responses/1';
        $response = $this->json('GET', $url);
        $response->assertStatus(200);
        $this->assertSame(5, $response->original);
    }
    /**
     * @test
     */
    public function 表示ポスト個別遷移前返信先ポスト存在確認：存在しない(): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $threads = Thread::factory()->count(1)->create();
        $posts = Post::factory()->count(5)->create();

        $url = '/api/exists/threads/1/responses/1';
        $response = $this->json('GET', $url);
        $response->assertStatus(200);
        $this->assertSame(0, $response->original);
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('responses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
