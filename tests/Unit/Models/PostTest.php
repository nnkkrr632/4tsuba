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

//DBのtruncateに使用
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class PostTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * @test
     */
    public function モデルがデータベースからレコードを取得できること、ファクトリーを使えることの確認()
    {
        //あらかじめユーザーとスレッドを作成する
        $thread = Thread::factory()->create();
        $post_model = app(Post::class);
        $this->assertEmpty($post_model->get());
        $count = 10;
        $post_collection = $post_model->factory()->count($count)->create();
        $this->assertNotEmpty($post_collection);
        $this->assertCount($count, $post_collection);
    }

    /**
     * @test
     */
    public function リレーションの確認【ポストはユーザーに所属する】()
    {
        $post_model = app(Post::class);
        $user_model = app(User::class);
        $thread = Thread::factory()->create();

        $user = $user_model->factory()->create()->first();
        $post = $post_model->factory()->setUserId($user->id)->count(1)->create()->first();
        //ポストからユーザーを取得してidを確認
        $this->assertSame($user->id, $post->user()->first()->id);
    }
    /**
     * @test
     */
    public function リレーションの確認【ポストはスレッドに所属する】()
    {
        $post_model = app(Post::class);
        $thread_model = app(User::class);
        $thread = Thread::factory()->create();

        $thread = $thread_model->factory()->create()->first();
        $post = $post_model->factory()->count(1)->create()->first();
        //ポストからスレッドを取得してidを確認
        $this->assertSame($thread->id, $post->thread()->first()->id);
    }

    /**
     * @test
     */
    public function リレーションの確認【ポストは画像を1つ持つ】()
    {
        $post_model = app(Post::class);
        $image_model = app(Image::class);

        $thread = Thread::factory()->create()->first();
        $post = $post_model->factory()->create()->first();
        $image = $image_model->factory()->create()->first();
        //ポストから画像を取得してidを確認
        $this->assertSame($image->id, $post->image()->first()->id);
    }
    /**
     * @test
     */
    public function リレーションの確認【ポストはいいねを復数持つ】()
    {
        $post_model = app(Post::class);
        $like_model = app(Like::class);
        $users = User::factory()->count(3)->create();
        $thread = Thread::factory()->create()->first();
        $post = $post_model->factory()->create()->first();
        LikeFactory::initializePostId();
        $like_model->factory()->setUserIdAndPostId($users[0]->id, $post->id)->create()->first();
        $like_model->factory()->setUserIdAndPostId($users[1]->id, $post->id)->create()->first();
        $like_model->factory()->setUserIdAndPostId($users[2]->id, $post->id)->create()->first();

        //ポストから画像を取得してidを確認
        $this->assertSame(3, $post->likes()->count());
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
