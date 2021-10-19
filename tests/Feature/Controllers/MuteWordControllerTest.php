<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\MuteWord;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use Illuminate\Support\Facades\DB;

class MuteWordControllerTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function ミュートワード取得()
    {
        //ユーザーをfactoryで作成
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        //ミュートワード作成(↑で作ったuser_idを使うためファクトリー未使用)
        $mute_word = MuteWord::create([
            'user_id' => $user->id,
            'mute_word' => 'index',
        ]);

        $response = $this->json('GET', '/api/mute_words');
        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson(
                [
                    [
                        'id' => $mute_word['id'],
                        'user_id' => $mute_word['user_id'],
                        'mute_word' => $mute_word['mute_word'],
                    ],
                ]
            );
    }
    /**
     * @test
     * @dataProvider storeMuteWordDataProvider
     */
    public function ミュートワード登録成功($key, $value): void
    {
        //ユーザーをfactoryで作成
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        $url = '/api/mute_words';
        $response = $this->json('POST', $url, [$key => $value]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('mute_words', [
            'user_id' => $user->id,
            'mute_word' => $value,
        ]);
    }
    /**
     * データプロバイダ(Store成功)
     * [key,value]
     */
    public function storeMuteWordDataProvider(): array
    {
        return [
            'ミュートワード登録' => ['mute_word', 'store'],
            'ミュートワード登録(1文字)' => ['mute_word', '🤔'],
            'ミュートワード登録(10文字)' => ['mute_word', '1234567890'],
        ];
    }

    /**
     * @test
     * @dataProvider notStoreMuteWordDataProvider
     */
    public function ミュートワード登録失敗($key, $value): void
    {
        //ユーザーをfactoryで作成
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        //ミュートワード作成(unique重複弾く用)
        MuteWord::create([
            'user_id' => $user->id,
            'mute_word' => 'unique違反',
        ]);

        $url = '/api/mute_words';
        $this->json('POST', $url, [$key => $value]);

        $response = $this->json('POST', $url, [$key => $value]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ(Store失敗)
     * [key,value]
     */
    public function notStoreMuteWordDataProvider(): array
    {
        return [
            'ミュートワード登録(null)' => ['mute_word', null],
            'ミュートワード登録(空文字)' => ['mute_word', ''],
            'ミュートワード登録(文字列「null」)' => ['mute_word', 'null'],
            'ミュートワード登録(HTMLタグを含む)' => ['mute_word', '<h1>'],
            'ミュートワード登録(11文字)' => ['mute_word', '12345678901'],
            'ミュートワード登録(既に登録済み)' => ['mute_word', 'unique違反'],
        ];
    }




    /**
     * @test
     */
    public function ミュートワード削除成功(): void
    {
        //ユーザーをfactoryで作成(teardownによりidは1になる)
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        //削除用mute_word作成(↑で作ったuser_idを使うためファクトリー未使用)
        $mute_word = MuteWord::create([
            'user_id' => $user->id,
            'mute_word' => '後で削除される',
        ]);

        $url = '/api/mute_words';
        $response = $this->json('DELETE', $url, ['id' => $mute_word['id']]);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('mute_words', [
            'id' => $mute_word->id,
        ]);
    }

    /**
     * @test
     * @dataProvider notDestroyMuteWordDataProvider
     */
    public function ミュートワード削除失敗(string $key, $value): void
    {
        //ユーザーをfactoryで作成(teardownによりidは1になる)
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        //ミュートワードをファクトリーで作成(他人のミュートワード)
        $mute_word = MuteWord::factory(1)->create()->first();

        $url = '/api/mute_words';
        $response = $this->json('DELETE', $url, [$key => $value]);
        $response->assertStatus(422);

        $this->assertDatabaseHas('mute_words', [
            'id' => $mute_word->id,
        ]);
    }

    /**
     * データプロバイダ(Destroy失敗)
     * [key,value]
     */
    public function notDestroyMuteWordDataProvider(): array
    {
        return [
            'ミュートワード削除(null)' => ['id', null],
            'ミュートワード削除(空文字)' => ['id', ''],
            'ミュートワード削除(not数字)' => ['id', 'aa'],
            //teardown()でテーブルをtruncateするから最初に作られるレコードのidは1になる
            'ミュートワード削除(他人のレコード)' => ['id', 1],
            'ミュートワード削除(未登録id)' => ['id', 123],
        ];
    }

    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('mute_words')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
