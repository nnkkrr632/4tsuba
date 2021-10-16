<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Image;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ThreadControllerTest extends TestCase
{
    /**
     * @test
     * @dataProvider storeThreadDataProvider_1
     */
    public function スレッド作成成功：画像なし($title, $body): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $url = '/api/threads';
        $response = $this->json('POST', $url, ['title' => $title, 'body' => $body]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('threads', [
            'title' => $title,
            'user_id' => $user->id,
        ])->assertDatabaseHas('posts', [
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => 1,
        ]);
    }
    /**
     * データプロバイダ
     * [$title, $body]
     */
    public function storeThreadDataProvider_1(): array
    {
        return [
            'スレッドタイトル(20文字)' => ['12345678901234567890', '書込'],
            '書込(200文字)' => ['スレッドタイトル', str_repeat("a", 200)],
        ];
    }

    /**
     * @test
     * @dataProvider storeThreadDataProvider_2
     */
    public function スレッド作成成功：画像あり($title, $body, $image): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        //フェイクのストレージを指定
        Storage::fake('local');

        $url = '/api/threads';
        $response = $this->json('POST', $url, ['title' => $title, 'body' => $body, 'image' => $image]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('threads', [
            'title' => $title,
            'user_id' => $user->id,
        ])->assertDatabaseHas('posts', [
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => 1,
        ])->assertDatabaseHas('images', [
            'thread_id' => 1,
            'post_id' => 1,
            'image_name' => $image->hashName(),
        ]);
        //ストレージ確認
        Storage::disk('local')->assertExists('public/images/' . $image->hashName());
    }
    /**
     * データプロバイダ
     * [$title, $body, $image]
     */
    public function storeThreadDataProvider_2(): array
    {
        $uploaded_image_1 = UploadedFile::fake()->image('image.jpg', 500, 500)->size(3000);
        $uploaded_image_2 = UploadedFile::fake()->image('image.jpeg', 500, 500)->size(3000);
        $uploaded_image_3 = UploadedFile::fake()->image('image.png', 500, 500)->size(3000);
        $uploaded_image_4 = UploadedFile::fake()->image('image.gif', 500, 500)->size(3000);

        return [
            '画像(jpg3MB)' => ['スレッドタイトル', '書込', $uploaded_image_1],
            '画像(jpeg3MB)' => ['スレッドタイトル', '書込', $uploaded_image_2],
            '画像(png3MB)' => ['スレッドタイトル', '書込', $uploaded_image_3],
            '画像(gif3MB)' => ['スレッドタイトル', '書込', $uploaded_image_4],
        ];
    }
    /**
     * @test
     * @dataProvider notStoreThreadDataProvider_1
     */
    public function スレッド作成失敗：画像なし($title, $body): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $url = '/api/threads';
        $response = $this->json('POST', $url, ['title' => $title, 'body' => $body]);
        $response->assertStatus(422);

        $this->assertDatabaseMissing('threads', [
            'title' => $title,
            'user_id' => $user->id,
        ])->assertDatabaseMissing('posts', [
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => 1,
        ]);
    }
    /**
     * データプロバイダ
     * [$title, $body]
     */
    public function notStoreThreadDataProvider_1(): array
    {
        return [
            'スレッドタイトル(null)' => [null, '書込'],
            'スレッドタイトル(文字列null)' => ['null', '書込'],
            'スレッドタイトル(21文字)' => ['123456789012345678901', '書込'],
            'スレッドタイトル(HTMLタグを含む)' => ['<h1>aaa</h1>', '書込'],
            '書込(null)' => ['スレッドタイトル', null],
            '書込(文字列null)' => ['スレッドタイトル', 'null'],
            '書込(201文字)' => ['スレッドタイトル', str_repeat("a", 201)],
            '書込(HTMLタグを含む)' => ['スレッドタイトル', '<h1>aaa</h1>'],
        ];
    }

    /**
     * @test
     * @dataProvider notStoreThreadDataProvider_2
     */
    public function スレッド作成失敗：画像あり($title, $body, $image): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        //フェイクのストレージを指定
        Storage::fake('local');

        $url = '/api/threads';
        $response = $this->json('POST', $url, ['title' => $title, 'body' => $body, 'image' => $image]);
        $response->assertStatus(422);

        //422の時点で、コントローラーでinsert文が流れていないのでなくて当然ではある
        $this->assertDatabaseMissing('threads', [
            'title' => $title,
            'user_id' => $user->id,
        ])->assertDatabaseMissing('posts', [
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => 1,
        ])->assertDatabaseMissing('images', [
            'thread_id' => 1,
            'post_id' => 1,
            //nullに対してhashName()ができないためコメントアウト
            //'image_name' => $image->hashName(),
        ]);
    }
    /**
     * データプロバイダ
     * [$title, $body, $image]
     */
    public function notStoreThreadDataProvider_2(): array
    {
        $uploaded_image_1 = UploadedFile::fake()->image('image.jpg', 500, 500)->size(3001);
        $uploaded_image_2 = UploadedFile::fake()->image('image.svg', 500, 500)->size(1000);
        $uploaded_image_3 = UploadedFile::fake()->image('image.js', 500, 500)->size(1000);

        return [
            '画像(jpg3MBより大きい)' => ['スレッドタイトル', '書込', $uploaded_image_1],
            '画像(未対応mime)' => ['スレッドタイトル', '書込', $uploaded_image_2],
            '画像(画像ファイルではない)' => ['スレッドタイトル', '書込', $uploaded_image_3],
            '画像(null)' => ['スレッドタイトル', '書込', null],
        ];
    }
    /**
     * @test
     * @dataProvider existsDataProvider
     */
    public function スレッド個別遷移前スレッド存在確認：存在する($thread_id): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $threads = Thread::factory()->count(5)->create();
        $url = '/api/exists/threads/' . $thread_id;
        $response = $this->json('GET', $url);
        $response->assertStatus(200);
        $this->assertSame(1, $response->original);
    }
    /**
     * データプロバイダ
     * [$thread_id]
     */
    public function existsDataProvider(): array
    {
        return [
            'スレッドid:1' => [1],
            'スレッドid:2' => [2],
            'スレッドid:3' => [3],
            'スレッドid:4' => [4],
            'スレッドid:5' => [5],
        ];
    }
    /**
     * @test
     * @dataProvider notExistsDataProvider
     */
    public function スレッド個別遷移前スレッド存在確認：存在しない($thread_id): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $threads = Thread::factory()->count(5)->create();
        $url = '/api/exists/threads/' . $thread_id;
        $response = $this->json('GET', $url);
        $response->assertStatus(200);
        $this->assertSame(0, $response->original);
    }
    /**
     * データプロバイダ
     * [$thread_id]
     */
    public function notExistsDataProvider(): array
    {
        return [
            'スレッドid:6(未存在スレッドid)' => [6],
            'スレッドid:7(未存在スレッドid)' => [7],
            'スレッドid:a(文字列)' => ['a'],
        ];
    }
    /**
     * @test
     * @dataProvider showDataProvider
     */
    public function スレッド個別成功($thread_id): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        //取得用スレッド&ポスト&画像を用意
        $threads = Thread::factory()->setPostCountAndLikeCount(5, 2)->count(5)->create();
        $posts = Post::factory()->setThreadId($thread_id)->count(5)->create();
        $images = Image::factory()->setThreadId($thread_id)->count(3)->create();
        $url = '/api/threads/' . $thread_id;
        $response = $this->json('GET', $url);
        $response->assertStatus(200)->assertJson(
            [
                'id' => $threads[$thread_id - 1]->id,
                'image_id' => $images[0]->id,
                'image_name' => $images[0]->image_name,
                'image_size' => $images[0]->image_size,
                'is_edited' => 0,
                'likes_count' => $threads[$thread_id - 1]->likes_count,
                'posts_count' => $threads[$thread_id - 1]->posts_count,
                'thread_id' => $threads[$thread_id - 1]->id,
                'title' => $threads[$thread_id - 1]->title,
                'user_id' => $threads[$thread_id - 1]->user_id,
                //updated_atはmodelのセッターが機能しないので省略
            ],
        );
    }
    /**
     * データプロバイダ
     * [$thread_id]
     */
    public function showDataProvider(): array
    {
        return [
            'スレッドid:1' => [1],
            'スレッドid:2' => [2],
            'スレッドid:3' => [3],
            'スレッドid:4' => [4],
            'スレッドid:5' => [5],
        ];
    }
    /**
     * @test
     * @dataProvider indexDataProvider
     */
    public function スレッド取得【index】($column, $desc_asc): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        //取得用にスレッド4件(スレッドid1~4, 各書込5, 画像なし, ポスト数ランダム, いいね数ランダム)
        for ($i = 0; $i < 4; $i++) {
            Thread::factory()->setPostCountAndLikeCount(mt_rand(1, 100), mt_rand(1, 100))->count(1)->create();
            Post::factory()->setThreadId($i + 1)->count(5)->create();
        }
        $url = '/api/threads/';
        $response = $this->json('GET', $url, ['column' => $column, 'desc_asc' => $desc_asc]);
        $response->assertStatus(200)->assertJsonCount(4);

        //order確認
        $expecting_thread_id_order_list = Thread::orderBy($column, $desc_asc)->pluck('id');
        $response_thread_id_order_list = $response->original->pluck('id');
        $this->assertJsonStringEqualsJsonString($expecting_thread_id_order_list, $response_thread_id_order_list);
    }
    /**
     * データプロバイダ
     * [$column, $desc_asc]
     */
    public function indexDataProvider(): array
    {
        return [
            //PHPUnitでつくると同じ時間になってしまうので諦め
            // '最終更新：asc' => ['updated_at', 'asc'],
            // '最終更新：desc' => ['updated_at', 'desc'],
            // '作成日時：asc' => ['created_at', 'asc'],
            // '作成日時：desc' => ['created_at', 'desc'],
            '書込数：asc' => ['posts_count', 'asc'],
            '書込数：desc' => ['posts_count', 'desc'],
            'いいね数：asc' => ['likes_count', 'asc'],
            'いいね数：desc' => ['likes_count', 'desc'],
        ];
    }
    /**
     * @test
     * @dataProvider notIndexDataProvider
     */

    public function スレッド取得失敗【index】($column, $desc_asc): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        //取得用にスレッド4件(スレッドid1~4, 各書込5, 画像なし, ポスト数ランダム, いいね数ランダム)
        for ($i = 0; $i < 4; $i++) {
            Thread::factory()->setPostCountAndLikeCount(mt_rand(1, 100), mt_rand(1, 100))->count(1)->create();
            Post::factory()->setThreadId($i + 1)->count(5)->create();
        }
        $url = '/api/threads/';
        $response = $this->json('GET', $url, ['column' => $column, 'desc_asc' => $desc_asc]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [$column, $desc_asc]
     */
    public function notIndexDataProvider(): array
    {
        return [
            'column(null)' => [null, 'asc'],
            'column(文字列null)' => ['null', 'asc'],
            'column(in以外)' => ['aaa', 'asc'],
            'asc_desc(null)' => ['created_at', null],
            'asc_desc(文字列null)' => ['created_at', 'null'],
            'asc_desc(in以外)' => ['created_at', 'aaa'],
        ];
    }

    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('images')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
