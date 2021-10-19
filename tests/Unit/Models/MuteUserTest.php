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
use App\Models\MuteUser;
use Database\Factories\LikeFactory;
use Database\Factories\MuteUserFactory;

//DBのtruncateに使用
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class MuteUserTest extends TestCase
{
    //use RefreshDatabase;

    /**
     * @test
     */
    public function モデルがデータベースからレコードを取得できること、ファクトリーを使えることの確認()
    {

        $mute_user_model = app(MuteUser::class);
        $this->assertEmpty($mute_user_model->get());
        $count = 10;
        $mute_user_collection = $mute_user_model->factory()->count($count)->create();
        $this->assertNotEmpty($mute_user_collection);
        $this->assertCount($count, $mute_user_collection);
    }

    /**
     * @test
     */
    public function リレーションの確認【いいねはユーザーに所属する】()
    {
        $mute_user_model = app(MuteUser::class);
        $user_model = app(User::class);
        $user = $user_model->factory()->create()->first();
        MuteUserFactory::initializeUserId();
        $mute_user = $mute_user_model->factory()->setUserId($user->id)->count(1)->create()->first();
        //ミュートユーザーからユーザーを取得してidを確認
        $this->assertSame($user->id, $mute_user->user()->first()->id);
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('mute_users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
