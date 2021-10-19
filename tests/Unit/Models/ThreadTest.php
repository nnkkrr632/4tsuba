<?php

namespace Tests\Unit\Models;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
//テストで使用するモデル
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Image;
use App\Models\Like;
use App\Models\Response;
use App\Models\MuteWord;
use App\Models\MuteUser;
use Database\Factories\ImageFactory;
//DBのtruncateに使用
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class ThreadTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * @test
     */
    public function モデルがデータベースからレコードを取得できること、ファクトリーを使えることの確認()
    {
        $thread_model = app(Thread::class);
        $this->assertEmpty($thread_model->get());
        $count = 10;
        $thread_collection = $thread_model->factory()->count($count)->create();
        $this->assertNotEmpty($thread_collection);
        $this->assertCount($count, $thread_collection);
    }

    /**
     * @test
     */
    public function リレーションの確認【スレッドはユーザーに所属する】()
    {
        $thread_model = app(Thread::class);
        $user_model = app(User::class);
        $user = $user_model->factory()->create()->first();
        $thread = $thread_model->factory()->setUserId($user->id)->count(1)->create()->first();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($user->id, $thread->user()->first()->id);
    }
    /**
     * @test
     */
    public function リレーションの確認【スレッドはポストを復数持つ】()
    {
        $count = 5;
        $thread_model = app(Thread::class);
        $post_model = app(Post::class);

        $user = User::factory()->count(1)->create()->first();
        $thread = $thread_model->factory()->setUserId($user->id)->create()->first();
        $posts = $post_model->factory()->count($count)->create();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($count, $thread->posts()->count());
    }
    /**
     * @test
     */
    public function リレーションの確認【スレッドは画像を復数持つ】()
    {
        $count = 5;
        $thread_model = app(Thread::class);
        $image_model = app(Image::class);

        $user = User::factory()->count(1)->create()->first();
        $thread = $thread_model->factory()->setUserId($user->id)->create()->first();
        $posts = Post::factory()->count(5)->create();
        ImageFactory::initializePostId();
        $images = $image_model->factory()->count($count)->create();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($count, $thread->images()->count());
    }
    /**
     * @test
     */
    public function リレーションの確認【スレッドは返信を復数持つ】()
    {
        $count = 5;
        $thread_model = app(Thread::class);
        $response_model = app(Response::class);

        $user = User::factory()->count(1)->create()->first();
        $thread = $thread_model->factory()->setUserId($user->id)->create();
        $posts = Post::factory()->count(10)->create();
        $response = $response_model->factory()->count($count)->create();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($count, $thread->responses()->count());
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('images')->truncate();
        DB::table('likes')->truncate();
        DB::table('responses')->truncate();
        DB::table('mute_words')->truncate();
        DB::table('mute_users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
