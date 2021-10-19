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
use Database\Factories\LikeFactory;
use Database\Factories\PostFactory;

//DBのtruncateに使用
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class LikeTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * @test
     */
    public function モデルがデータベースからレコードを取得できること、ファクトリーを使えることの確認()
    {
        //あらかじめユーザーとスレッドを作成する
        $thread = Thread::factory()->create();
        $posts = Post::factory()->count(10)->create();
        LikeFactory::initializePostId();

        $like_model = app(Like::class);
        $this->assertEmpty($like_model->get());
        $count = 10;
        $like_collection = $like_model->factory()->count($count)->create();
        $this->assertNotEmpty($like_collection);
        $this->assertCount($count, $like_collection);
    }

    /**
     * @test
     */
    public function リレーションの確認【いいねはユーザーに所属する】()
    {
        $like_model = app(Like::class);
        $user_model = app(User::class);
        $thread = Thread::factory()->create();
        $posts = Post::factory()->count(10)->create();
        $user = $user_model->factory()->create()->first();
        LikeFactory::initializePostId();
        $like = $like_model->factory()->setUserId($user->id)->count(1)->create()->first();
        //いいねからユーザーを取得してidを確認
        $this->assertSame($user->id, $like->user()->first()->id);
    }
    /**
     * @test
     */
    public function リレーションの確認【いいねはポストに所属する】()
    {
        $like_model = app(Like::class);
        $post_model = app(Post::class);
        $thread = Thread::factory()->create();
        $post = $post_model->factory()->count(1)->create()->first();
        LikeFactory::initializePostId();
        $like = $like_model->factory()->count(1)->create()->first();
        //いいねからポストを取得してidを確認
        $this->assertSame($post->id, $like->post()->first()->id);
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('images')->truncate();
        DB::table('likes')->truncate();
        DB::table('mute_words')->truncate();
        DB::table('mute_users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
