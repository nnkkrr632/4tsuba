<?php

namespace Tests\Unit\Models;

//use PHPUnit\Framework\TestCase;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
//テストで使用するモデル
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Like;
use App\Models\MuteWord;
use App\Models\MuteUser;
//DBのtruncateに使用
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class UserTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * @test
     */
    public function モデルがデータベースからレコードを取得できること、ファクトリーを使えることの確認()
    {
        $user_model = app(User::class);
        $this->assertEmpty($user_model->get());
        $count = 10;
        $user_collection = $user_model->factory()->count($count)->create();
        $this->assertNotEmpty($user_collection);
        $this->assertCount($count, $user_collection);
    }

    /**
     * @test
     */
    public function リレーションの確認【ユーザーはスレッドを復数持つ】()
    {
        $count = 5;
        $user_model = app(User::class);
        $thread_model = app(Thread::class);
        $user = $user_model->factory()->create()->first();
        //スレッドを作成(user_idを$userで指定)
        $threads = $thread_model->factory()->setUserId($user->id)->count($count)->create();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($count, $user->threads()->count());
    }
    /**
     * @test
     */
    public function リレーションの確認【ユーザーはポストを復数持つ】()
    {
        $count = 5;
        $user_model = app(User::class);
        $post_model = app(Post::class);
        $thread = Thread::factory()->count(1)->create()->first();

        $user = $user_model->factory()->create()->first();
        $posts = $post_model->factory()->setUserId($user->id)->count($count)->create();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($count, $user->posts()->count());
    }
    /**
     * @test
     */
    public function リレーションの確認【ユーザーはいいねを復数持つ】()
    {
        $count = 5;
        $user_model = app(User::class);
        $like_model = app(Like::class);
        $thread = Thread::factory()->count(1)->create()->first();
        $posts = Post::factory()->count(10)->create();

        $user = $user_model->factory()->create()->first();
        //いいねを作成(user_idを$userで指定)
        $likes = $like_model->factory()->setUserId($user->id)->count($count)->create();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($count, $user->likes()->count());
    }
    /**
     * @test
     */
    public function リレーションの確認【ユーザーはミュートワードを復数持つ】()
    {
        $count = 5;
        $user_model = app(User::class);
        $mute_word_model = app(MuteWord::class);

        $user = $user_model->factory()->create()->first();
        $mute_words = $mute_word_model->factory()->setUserId($user->id)->count($count)->create();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($count, $user->mute_words()->count());
    }
    /**
     * @test
     */
    public function リレーションの確認【ユーザーはミュートユーザーを復数持つ】()
    {
        $count = 5;
        $user_model = app(User::class);
        $mute_user_model = app(MuteUser::class);

        $user = $user_model->factory()->create()->first();
        $other_user = User::factory()->count(10)->create();
        $mute_users = $mute_user_model->factory()->setUserId($user->id)->count($count)->create();
        //ユーザーからスレッドを取得して件数を確認
        $this->assertSame($count, $user->mute_users()->count());
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
