<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Like;
use App\Models\Thread;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use App\Models\FormRequestMessage;

class LikeControllerTest extends TestCase
{
    /**
     * @test
     */
    public function いいね登録成功(): void
    {
        $users = User::factory()->count(10)->create();
        $user = $users->random(1)[0];
        $thread = Thread::factory()->count(1)->create()->first();
        $likes_count = $thread->likes_count;
        //ポストfactoryはスレッドid 1固定 ユーザーランダム
        $posts = Post::factory(10)->create();
        $post = $posts->random(1)[0];
        $this->actingAs($user);

        $url = '/api/like';
        $response = $this->json('PUT', $url, ['thread_id' => $post->thread_id, 'post_id' => $post->id]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ])->assertDatabaseHas('threads', [
            'id' => $post->thread_id,
            'likes_count' => $likes_count + 1,
        ]);
    }
    /**
     * @test
     * @dataProvider notStoreLikeDataProvider
     */
    public function いいね登録失敗「型関係」($thread_id, $post_id): void
    {
        $users = User::factory()->count(10)->create();
        $user = $users->random(1)[0];
        $thread = Thread::factory()->count(1)->create()->first();
        $likes_count = $thread->likes_count;
        //ポストfactoryはスレッドid 1固定 ユーザーランダム
        $posts = Post::factory(10)->create();
        $post = $posts->random(1)[0];
        $this->actingAs($user);

        $url = '/api/like';
        $response = $this->json('PUT', $url, ['thread_id' => $thread_id, 'post_id' => $post_id]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ(Store失敗)
     * [$thread_id, $post_id]
     */
    public function notStoreLikeDataProvider(): array
    {
        return [
            'スレッドid(null)' => [null, 1],
            'スレッドid(文字列null)' => ['null', 1],
            'スレッドid(文字列)' => ['aaa', 1],
            'スレッドid(存在しない)' => [123, 1],
            'ポストid(null)' => [1, null],
            'ポストid(文字列null)' => [1, 'null'],
            'ポストid(文字列)' => [1, 'aaa'],
            'ポストid(存在しない)' => [1, 123],
        ];
    }
    /**
     * @test
     */
    public function いいね登録失敗「既に登録済み」(): void
    {
        $users = User::factory()->count(10)->create();
        $user = $users->random(1)[0];
        $thread = Thread::factory()->count(1)->create()->first();
        $likes_count = $thread->likes_count;
        //ポストfactoryはスレッドid 1固定 ユーザーランダム
        $posts = Post::factory(10)->create();
        $post = $posts->random(1)[0];
        $this->actingAs($user);

        $url = '/api/like';
        //1回目いいね登録
        $this->json('PUT', $url, ['thread_id' => $post->thread_id, 'post_id' => $post->id]);

        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->alreadyLike('書込');
        //2回めいいね登録
        $response = $this->json('PUT', $url, ['thread_id' => $post->thread_id, 'post_id' => $post->id]);
        $response->assertStatus(422)->assertJson([
            'errors' => ['post_id' => [$expecting_message]],
            'message' => 'The given data was invalid.',
        ]);
    }
    /**
     * @test
     */
    public function いいね削除成功(): void
    {
        $users = User::factory()->count(10)->create();
        $user = $users->random(1)[0];
        $thread = Thread::factory()->count(1)->create()->first();
        $likes_count = $thread->likes_count;
        //ポストfactoryはスレッドid 1固定 ユーザーランダム
        $posts = Post::factory(10)->create();
        $post = $posts->random(1)[0];
        $this->actingAs($user);

        $url = '/api/like';
        //先んじていいね登録
        $this->json('PUT', $url, ['thread_id' => $post->thread_id, 'post_id' => $post->id]);
        //↑に対していいね削除
        $response = $this->json('DELETE', $url, ['thread_id' => $post->thread_id, 'post_id' => $post->id]);
        $response->assertStatus(200);
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ])->assertDatabaseHas('threads', [
            'id' => $post->thread_id,
            'likes_count' => $likes_count,
        ]);
    }
    /**
     * @test
     * @dataProvider notDestroyLikeDataProvider
     */
    public function いいね削除失敗「型関係」($thread_id, $post_id): void
    {
        $users = User::factory()->count(10)->create();
        $user = $users->random(1)[0];
        $thread = Thread::factory()->count(1)->create()->first();
        $likes_count = $thread->likes_count;
        //ポストfactoryはスレッドid 1固定 ユーザーランダム
        $posts = Post::factory(10)->create();
        $post = $posts->random(1)[0];
        $this->actingAs($user);

        $url = '/api/like';
        //先んじていいね登録
        $this->json('PUT', $url, ['thread_id' => $post->thread_id, 'post_id' => $post->id]);
        //↑に対していいね削除
        $response = $this->json('DELETE', $url, ['thread_id' => $thread_id, 'post_id' => $post_id]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ(Store失敗)
     * [$thread_id, $post_id]
     */
    public function notDestroyLikeDataProvider(): array
    {
        return [
            'スレッドid(null)' => [null, 1],
            'スレッドid(文字列null)' => ['null', 1],
            'スレッドid(文字列)' => ['aaa', 1],
            'スレッドid(存在しない)' => [123, 1],
            'ポストid(null)' => [1, null],
            'ポストid(文字列null)' => [1, 'null'],
            'ポストid(文字列)' => [1, 'aaa'],
            'ポストid(存在しない)' => [1, 123],
        ];
    }
    /**
     * @test
     */
    public function いいね削除失敗「そもそもいいねしていない」(): void
    {
        $users = User::factory()->count(10)->create();
        $user = $users->random(1)[0];
        $thread = Thread::factory()->count(1)->create()->first();
        $likes_count = $thread->likes_count;
        //ポストfactoryはスレッドid 1固定 ユーザーランダム
        $posts = Post::factory(10)->create();
        $post = $posts->random(1)[0];
        $this->actingAs($user);

        $url = '/api/like';

        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->notLike('書込');
        //未いいね登録に対していいね削除
        $response = $this->json('DELETE', $url, ['thread_id' => $post->thread_id, 'post_id' => $post->id]);
        $response->assertStatus(422)->assertJson([
            'errors' => ['post_id' => [$expecting_message]],
            'message' => 'The given data was invalid.',
        ]);
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('likes')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
