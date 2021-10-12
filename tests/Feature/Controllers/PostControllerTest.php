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

class PostControllerTest extends TestCase
{
    /**
     * @test
     * @dataProvider storePostDataProvider_1
     */
    public function ポスト作成成功：画像なし($thread_id, $body): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();

        $url = '/api/posts';
        $response = $this->json('POST', $url, ['thread_id' => $thread_id, 'body' => $body]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ]);
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
    /**
     * @test
     */
    public function ポスト作成成功：NGワード含む(): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();

        $url = '/api/posts';
        $response = $this->json('POST', $url, ['thread_id' => 1, 'body' => 'うんこ']);
        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'body' => '🍀🍀🍀',
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ]);
    }
    /**
     * @test
     * @dataProvider storePostDataProvider_2
     */
    public function ポスト作成成功：画像あり($thread_id, $body, $image): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();

        //フェイクのストレージを指定
        Storage::fake('local');

        $url = '/api/posts';
        $response = $this->json('POST', $url, ['thread_id' => $thread_id, 'body' => $body, 'image' => $image]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ])->assertDatabaseHas('images', [
            'thread_id' => $thread->id,
            'post_id' => 1,
            'image_name' => $image->hashName(),
        ]);
        //ストレージ確認
        Storage::disk('local')->assertExists('public/images/' . $image->hashName());
    }
    /**
     * データプロバイダ
     * [$thread_id, $body, $image]
     */
    public function storePostDataProvider_2(): array
    {
        $uploaded_image_1 = UploadedFile::fake()->image('image.jpg', 500, 500)->size(3000);
        $uploaded_image_2 = UploadedFile::fake()->image('image.jpeg', 500, 500)->size(3000);
        $uploaded_image_3 = UploadedFile::fake()->image('image.png', 500, 500)->size(3000);
        $uploaded_image_4 = UploadedFile::fake()->image('image.gif', 500, 500)->size(3000);

        return [
            '画像(jpg3MB)' => [1, '書込', $uploaded_image_1],
            '画像(jpeg3MB)' => [1, '書込', $uploaded_image_2],
            '画像(png3MB)' => [1, '書込', $uploaded_image_3],
            '画像(gif3MB)' => [1, '書込', $uploaded_image_4],
        ];
    }
    /**
     * @test
     * @dataProvider notStorePostDataProvider_1
     */
    public function ポスト作成失敗：画像なし($thread_id, $body): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();

        $url = '/api/posts';
        $response = $this->json('POST', $url, ['thread_id' => $thread_id, 'body' => $body]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [$thread_id, $body]
     */
    public function notStorePostDataProvider_1(): array
    {
        return [
            'スレッドid(null)' => [null, '書込'],
            'スレッドid(文字列null)' => ['null', '書込'],
            'スレッドid(文字列)' => ['aaa', '書込'],
            'スレッドid(存在しない)' => [123, '書込'],
            '書込(null)' => [1, null],
            '書込(文字列null)' => [1, 'null'],
            '書込(201文字)' => [1, str_repeat("a", 201)],
            '書込(HTMLタグを含む)' => [1, '<h1>aaa</h1>'],
        ];
    }
    /**
     * @test
     * @dataProvider notStorePostDataProvider_2
     */
    public function ポスト作成失敗：画像あり($thread_id, $body, $image): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();

        //フェイクのストレージを指定
        Storage::fake('local');

        $url = '/api/posts';
        $response = $this->json('POST', $url, ['thread_id' => $thread_id, 'body' => $body, 'image' => $image]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [$thread_id, $body, $image]
     */
    public function notStorePostDataProvider_2(): array
    {
        $uploaded_image_1 = UploadedFile::fake()->image('image.jpg', 500, 500)->size(3001);
        $uploaded_image_2 = UploadedFile::fake()->image('image.svg', 500, 500)->size(1000);
        $uploaded_image_3 = UploadedFile::fake()->image('image.js', 500, 500)->size(1000);

        return [
            '画像(jpg3MBより大きい)' => [1, '書込', $uploaded_image_1],
            '画像(未対応mime)' => [1, '書込', $uploaded_image_2],
            '画像(画像ファイルではない)' => [1, '書込', $uploaded_image_3],
            '画像(null)' => [1, '書込', null],
        ];
    }






















    /**
     * @test
     * @dataProvider editPostDataProvider_1
     */
    public function ポスト編集成功：画像なし($id, $thread_id, $body): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        //編集されるポストをあらかじめ作成
        $post = Post::factory()->setUserId($user->id)->count(1)->create()->first();
        $another_post = Post::factory()->setUserId($another_user->id)->count(1)->create()->first();

        $url = '/api/posts/edit';
        $response = $this->json('POST', $url, ['id' => $id, 'thread_id' => $thread_id, 'body' => $body]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ]);
    }
    /**
     * データプロバイダ
     * [$id, $thread_id, $body]
     */
    public function editPostDataProvider_1(): array
    {
        return [
            'スレッドid(1)' => [1, 1, '編集後'],
            '書込(200文字)' => [1, 1,  str_repeat("a", 200)],
        ];
    }
    /**
     * @test
     */
    public function ポスト編集成功：NGワード含む(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        //編集されるポストをあらかじめ作成
        $post = Post::factory()->setUserId($user->id)->count(1)->create()->first();
        $another_post = Post::factory()->setUserId($another_user->id)->count(1)->create()->first();

        $url = '/api/posts/edit';
        $response = $this->json('POST', $url, ['id' => 1, 'thread_id' => 1, 'body' => 'うんこ']);
        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'body' => '🍀🍀🍀',
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ]);
    }
    /**
     * @test
     * @group miss
     * @dataProvider editPostDataProvider_3
     */
    public function ポスト編集成功：画像削除チェック「画像既にあり時」($id, $thread_id, $body, $delete_image): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();

        //フェイクのストレージを指定
        Storage::fake('local');
        //1回目のポスト(作成)
        $url = '/api/posts';
        $uploaded_image_1 = UploadedFile::fake()->image('image.jpg', 500, 500)->size(3000);
        $reponse = $this->json('POST', $url, ['thread_id' => $thread_id, 'body' => $body, 'image' => $uploaded_image_1]);
        //ストレージ確認
        Storage::disk('local')->assertExists('public/images/' . $uploaded_image_1->hashName());

        //画像削除にチェック
        $url = '/api/posts/edit';
        $response = $this->json('POST', $url, ['id' => $id, 'thread_id' => $thread_id, 'body' => $body, 'delete_image' => $delete_image]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'id' => $id,
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => $thread_id,
        ])->assertDatabaseMissing('images', [
            'thread_id' => $thread_id,
            'post_id' => $id,
            'image_name' => $uploaded_image_1->hashName(),
        ]);
        //ストレージ確認
        Storage::disk('local')->assertMissing('public/images/' . $uploaded_image_1->hashName());
    }
    /**
     * データプロバイダ
     * [$id, $thread_id, $body]
     */
    public function editPostDataProvider_3(): array
    {
        return [
            // 'スレッド削除チェックあり(true)' => [1, 1, '編集後', true],
            'スレッド削除チェックあり(文字列true)' => [1, 1, '編集後', 'true'],
        ];
    }

    /**
     * @test
     * @dataProvider editPostDataProvider_2
     */
    public function ポスト編集成功：画像あり($id, $thread_id, $body, $image): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        //編集されるポストをあらかじめ作成
        $post = Post::factory()->setUserId($user->id)->count(1)->create()->first();
        $another_post = Post::factory()->setUserId($another_user->id)->count(1)->create()->first();

        //フェイクのストレージを指定
        Storage::fake('local');

        $url = '/api/posts/edit';
        $response = $this->json('POST', $url, ['id' => $id, 'thread_id' => $thread_id, 'body' => $body, 'image' => $image]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'body' => $body,
            'user_id' => $user->id,
            'thread_id' => $thread->id,
        ])->assertDatabaseHas('images', [
            'thread_id' => $thread->id,
            'post_id' => $id,
            'image_name' => $image->hashName(),
        ]);
        //ストレージ確認
        Storage::disk('local')->assertExists('public/images/' . $image->hashName());
    }
    /**
     * データプロバイダ
     * [$id, $thread_id, $body, $image]
     */
    public function editPostDataProvider_2(): array
    {
        $uploaded_image_1 = UploadedFile::fake()->image('image.jpg', 500, 500)->size(3000);
        $uploaded_image_2 = UploadedFile::fake()->image('image.jpeg', 500, 500)->size(3000);
        $uploaded_image_3 = UploadedFile::fake()->image('image.png', 500, 500)->size(3000);
        $uploaded_image_4 = UploadedFile::fake()->image('image.gif', 500, 500)->size(3000);

        return [
            '画像(jpg3MB)' => [1, 1, '編集後', $uploaded_image_1],
            '画像(jpeg3MB)' => [1, 1, '編集後', $uploaded_image_2],
            '画像(png3MB)' => [1, 1, '編集後', $uploaded_image_3],
            '画像(gif3MB)' => [1, 1, '編集後', $uploaded_image_4],
        ];
    }
    /**
     * @test
     * @dataProvider notEditPostDataProvider_1
     */
    public function ポスト編集失敗：画像なし($id, $thread_id, $body): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        //編集されるポストをあらかじめ作成
        $post = Post::factory()->setUserId($user->id)->count(1)->create()->first();
        $another_post = Post::factory()->setUserId($another_user->id)->count(1)->create()->first();

        $url = '/api/posts/edit';
        $response = $this->json('POST', $url, ['id' => $id, 'thread_id' => $thread_id, 'body' => $body]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [$id, $thread_id, $body]
     */
    public function notEditPostDataProvider_1(): array
    {
        return [
            'id(null)' => [null, 1, '編集後'],
            'id(文字列null)' => ['null', 1, '編集後'],
            'id(文字列)' => ['aaa', 1, '編集後'],
            'id(存在しない)' => [123, 1, '編集後'],
            'id(存在するが別の人が作成)' => [2, 1, '編集後'],
            'スレッドid(null)' => [1, null, '編集後'],
            'スレッドid(文字列null)' => [1, 'null', '編集後'],
            'スレッドid(文字列)' => [1, 'aaa', '編集後'],
            'スレッドid(存在しない)' => [1, 123, '編集後'],
            '書込(null)' => [1, 1, null],
            '書込(文字列null)' => [1, 1, 'null'],
            '書込(201文字)' => [1, 1, str_repeat("a", 201)],
            '書込(HTMLタグを含む)' => [1, 1, '<h1>aaa</h1>'],
        ];
    }
    /**
     * @test
     * @dataProvider notEditPostDataProvider_2
     */
    public function ポスト編集失敗：画像あり($id, $thread_id, $body, $image): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        //編集されるポストをあらかじめ作成
        $post = Post::factory()->setUserId($user->id)->count(1)->create()->first();
        $another_post = Post::factory()->setUserId($another_user->id)->count(1)->create()->first();

        //フェイクのストレージを指定
        Storage::fake('local');

        $url = '/api/posts/edit';
        $response = $this->json('POST', $url, ['id' => $id, 'thread_id' => $thread_id, 'body' => $body, 'image' => $image]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [$id, $thread_id, $body, $image]
     */
    public function notEditPostDataProvider_2(): array
    {
        $uploaded_image_1 = UploadedFile::fake()->image('image.jpg', 500, 500)->size(3001);
        // $uploaded_image_2 = UploadedFile::fake()->image('image.svg', 500, 500)->size(1000);
        // $uploaded_image_3 = UploadedFile::fake()->image('image.js', 500, 500)->size(1000);

        return [
            '画像(jpg3MBより大きい)' => [1, 1, '編集後', $uploaded_image_1],
            // '画像(未対応mime)' => [1, 1, '編集後', $uploaded_image_2],
            // '画像(画像ファイルではない)' => [1, 1, '編集後', $uploaded_image_3],
            '画像(null)' => [1, 1, '編集後', null],
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
