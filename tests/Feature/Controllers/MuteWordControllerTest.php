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

class MuteWordControllerTest extends TestCase
{
    // テストデータのリセット
    //これをすることで全てのテーブルをリセットする。つまり↓でユーザーを作成するとき必然的にuserのidが１になる。
    //mute_wordsのfactoryではuser_idを1に固定しているが、これは、
    //mute_words機能はユーザーを固定する必要があるため。user_idをランダムにするとこのテストで
    //mute_wordsの件数やidが特定できなくなる。
    use RefreshDatabase;

    /** @test */
    public function ミュートワード取得()
    {
        //ユーザーをfactoryで作成
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        // テストデータをFactoryで作成
        $mute_words = MuteWord::factory(3)->create();

        $response = $this->json('GET', '/api/mute_words');
        $response->assertStatus(200)->assertJsonCount(3)
            ->assertJson(
                [
                    [
                        'id' => $mute_words[2]['id'],
                        'user_id' => $mute_words[2]['user_id'],
                        'mute_word' => $mute_words[2]['mute_word'],
                    ],
                    [
                        'id' => $mute_words[1]['id'],
                        'user_id' => $mute_words[1]['user_id'],
                        'mute_word' => $mute_words[1]['mute_word'],
                    ],
                    [
                        'id' => $mute_words[0]['id'],
                        'user_id' => $mute_words[0]['user_id'],
                        'mute_word' => $mute_words[0]['mute_word'],
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
     * @test
     * @dataProvider notStoreMuteWordDataProvider
     */
    public function ミュートワード登録失敗($key, $value): void
    {
        //ユーザーをfactoryで作成
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        $url = '/api/mute_words';
        //削除するためのmute_wordを作成
        $this->json('POST', $url, [$key => $value]);

        $response = $this->json('POST', $url, [$key => $value]);
        $response->assertStatus(422);

        $this->assertDatabaseMissing('mute_words', [
            'user_id' => $user->id,
            'mute_word' => $value,
        ]);
    }
    /**
     * @test
     * @dataProvider deleteMuteWordDataProvider
     */
    public function ミュートワード削除成功($key, $value): void
    {
        //ユーザーをfactoryで作成
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);
        //削除用mute_word作成(ファクトリーだと↑で作ったuseridでつくれないから普通に作成)
        $mute_word = MuteWord::create([
            'user_id' => $user->id,
            'mute_word' => '野菜',
        ]);

        $url = '/api/mute_words';
        $response = $this->json('DELETE', $url, ['id' => $mute_word->id]);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('mute_words', [
            'id' => $mute_word->id,
        ]);
    }

    /**
     * @test
     * 
     */
    public function ミュートワード削除失敗(): void
    {
        //ユーザーをfactoryで作成
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        //削除用mute_word作成(ファクトリーだと↑で作ったuseridでつくれないから普通に作成)
        $mute_word = MuteWord::create([
            'user_id' => $user->id,
            'mute_word' => '野菜2',
        ]);

        $url = '/api/mute_words';
        $response = $this->json('DELETE', $url, ['id' => 'aaa']);
        $response->assertStatus(422);

        $this->assertDatabaseHas('mute_words', [
            'id' => 'aaa',
        ]);
    }

    /**
     * データプロバイダ
     * [key,value]
     */
    public function storeMuteWordDataProvider(): array
    {
        return [
            'ミュートワード登録' => ['mute_word', '野菜'],
            'ミュートワード登録(1文字)' => ['mute_word', 'a'],
            'ミュートワード登録(10文字)' => ['mute_word', '1234567890'],
        ];
    }
    /**
     * データプロバイダ
     * [key,value]
     */
    public function notStoreMuteWordDataProvider(): array
    {
        return [
            'ミュートワード登録(null)' => ['mute_word', null],
            'ミュートワード登録(空文字)' => ['mute_word', ''],
            'ミュートワード登録(11文字)' => ['mute_word', '12345678901'],
        ];
    }
    /**
     * データプロバイダ
     * [key,value]
     */
    public function deleteMuteWordDataProvider(): array
    {
        return [
            'ミュートワード削除(id:1)' => ['id', 1],
        ];
    }
    /**
     * データプロバイダ
     * [key,value]
     */
    public function notDeleteMuteWordDataProvider(): array
    {
        return [
            'ミュートワード削除(null)' => ['id', null],
            'ミュートワード削除(空文字)' => ['id', ''],
            'ミュートワード削除(not数字)' => ['id', 'aa'],
            'ミュートワード削除(未登録id)' => ['id', 123],
        ];
    }
}
