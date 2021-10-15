<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Image;
use App\Models\Like;
use App\Models\MuteUser;
use App\Models\MuteWord;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostControllerIndexMethodTest extends TestCase
{
    /**
     * @test
     */
    public function ポスト取得：スレッド個別(): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        $posts = Post::factory()->count(10)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'thread_id', 'value' => '1']);
        $response->assertStatus(200)->assertJsonCount(10);
        $array = $response->json();
        $posts_thread_id = array_column($array, 'displayed_post_id');
        // $type =  (string)gettype($response[0]);
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $posts_thread_id);
    }
    /**
     * データプロバイダ
     * [$thread_id, $body]
     */
    public function storePostDataProvider_1(): array
    {
        return [
            'スレッドid(1)' => [1, '書込'],
            '書込(200文字)' => [1, str_repeat("a", 200)],
        ];
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('images')->truncate();
        DB::table('responses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
