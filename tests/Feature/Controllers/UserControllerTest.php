<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;

class UserControllerTest extends TestCase
{
    // データベースの初期化にトランザクションを使う
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    //returnUserInfo($request)のテスト
    public function testReturnUserInfo()
    {
        //まずfactoryでデータ生成
        $user = User::factory(1)->create();

        // GET リクエスト
        $response = $this->get('api/users/' . $user->id);

        // レスポンスの検証
        $response->assertOk()  # ステータスコードが 200
            ->assertJsonCount(1) # レスポンスの配列の件数が1件
            ->assertJsonFragment([ # レスポンスJSON に以下の値を含む
                'email' => 'user1@example.com',
            ]);
    }
}
