<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
//テスト後にテーブルDelete
use Illuminate\Foundation\Testing\RefreshDatabase;
//テストで使用するモデル
use App\Models\Like;
use App\Models\User;
use App\Models\Post;
//factoryを使用する
use Database\Factories;


class UserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic unit test example.
     *
     * @test
     */
    //modelのテスト
    public function testFactoryable()
    {
        $eloquent = app(User::class);
        $this->assertEmpty($eloquent->get()); // 初期状態では空であることを確認
        $entity = $eloquent->factory(User::class)->create(); // 先程作ったファクトリーでレコード生成
        $this->assertNotEmpty($eloquent->get()); // 再度getしたら中身が空ではないことを確認し、ファクトリ可能であることを保証
    }

    // 下記のテスト追加
    public function testUserHasManyLikes()
    {
        $count = 5;
        $userEloquent = app(User::class);
        $likeEloquent = app(Like::class);
        $user = $userEloquent->factory(User::class)->create(); // ユーザーを作成
        $likes = $likeEloquent->factory(Like::class, $count)->create([
            'user_id' => $user->id,
            'post_id' => Post::all()->random()->id,
        ]); // ユーザーに紐づくいいねレコードを作成 （create の引数に指定するとその値でデータ作成される）
        // refresh() で再度同じレコードを取得しなおし、リレーション先の件数が作成した件数と一致することを確認し、リレーションが問題ないことを保証
        $this->assertEquals($count, count($user->refresh()->likes));
    }
}
