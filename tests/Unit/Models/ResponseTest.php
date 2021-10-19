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


class ResponseTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * @test
     */
    public function モデルがデータベースからレコードを取得できること、ファクトリーを使えることの確認()
    {
        $thread = Thread::factory()->count(1)->create();
        $posts = Post::factory()->count(10)->create();

        $response_model = app(Response::class);
        $this->assertEmpty($response_model->get());
        $count = 10;
        $response_collection = $response_model->factory()->count($count)->create();
        $this->assertNotEmpty($response_collection);
        $this->assertCount($count, $response_collection);
    }

    /**
     * @test
     */
    public function リレーションの確認【返信はスレッドに所属する】()
    {
        $response_model = app(Response::class);
        $user_model = app(User::class);
        $user = $user_model->factory()->create()->first();

        $thread = Thread::factory()->count(1)->create()->first();
        $posts = Post::factory()->count(10)->create();

        $response = $response_model->factory()->count(1)->create()->first();
        //返信からスレッドを取得してidを確認
        $this->assertSame($thread->id, $response->thread()->first()->id);
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
