<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\MuteUser;
use App\Models\FormRequestMessage;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use Illuminate\Support\Facades\DB;

class MuteUserControllerTest extends TestCase
{
    //use RefreshDatabase;


    /** @test */
    public function ミュートユーザー取得()
    {
        //ユーザーをfactoryで作成
        $users = User::factory(2)->create();
        $muting_user = $users[0];
        $muted_user = $users[1];
        $this->actingAs($muting_user);

        //ミュートユーザー作成(↑で作ったuser_idを使うためファクトリー未使用)
        $mute_user = MuteUser::create([
            'muting_user_id' => $muting_user->id,
            'user_id' => $muted_user->id,
        ]);

        $response = $this->json('GET', '/api/mute_users');
        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson(
                [
                    [
                        'id' => $mute_user['id'],
                        'user_id' => $muted_user['id'],
                        'name' => $muted_user['name'],
                        'icon_name' => 'no_image.png',
                    ],
                ]
            );
    }
    /**
     * @test
     */
    public function ミュートユーザー登録成功(): void
    {
        //ユーザーをfactoryで作成
        $users = User::factory(2)->create();
        $muting_user = $users[0];
        $muted_user = $users[1];
        $this->actingAs($muting_user);

        $url = '/api/mute_users';
        $response = $this->json('PUT', $url, ['user_id' => $muted_user->id]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('mute_users', [
            'muting_user_id' => $muting_user->id,
            'user_id' => $muted_user->id,
        ]);
    }
    /**
     * @test
     * @dataProvider notStoreMuteUserDataProvider
     */
    public function ミュートユーザー登録失敗「型関係」($key, $value): void
    {
        //ユーザーをfactoryで作成
        $users = User::factory(1)->create();
        $muting_user = $users[0];
        $this->actingAs($muting_user);

        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->cancel('ミュートユーザー');

        $url = '/api/mute_users';
        $response = $this->json('PUT', $url, [$key => $value]);
        $response->assertStatus(422)->assertJsonFragment([
            'user_id' => [$expecting_message]
        ]);

        //エラーメッセージ確認
        // $error_message = $response['errors']['user_id'][0];
        // $this->assertSame($expecting_message, $error_message);
    }
    /**
     * データプロバイダ(Store失敗)
     * [key,value]
     */
    public function notStoreMuteUserDataProvider(): array
    {
        return [
            'ミュートユーザー登録(null)' => ['user_id', null],
            'ミュートユーザー登録(空文字)' => ['user_id', ''],
            'ミュートユーザー登録(文字列「null」)' => ['user_id', 'null'],
            'ミュートユーザー登録(文字列)' => ['user_id', 'aaa'],
        ];
    }
    /**
     * @test
     */
    public function ミュートユーザー登録失敗「自分」(): void
    {
        //ユーザーをfactoryで作成
        $users = User::factory(1)->create();
        $muting_user = $users[0];
        $this->actingAs($muting_user);

        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->cancel('ミュートユーザー');

        $url = '/api/mute_users';
        $response = $this->json('PUT', $url, ['user_id' => $muting_user->id]);
        $response->assertStatus(422)->assertJsonFragment([
            'user_id' => [$expecting_message]
        ]);
    }
    /**
     * @test
     */
    public function ミュートユーザー登録失敗「既に登録済み」(): void
    {
        //ユーザーをfactoryで作成
        $users = User::factory(2)->create();
        $muting_user = $users[0];
        $muted_user = $users[1];
        $this->actingAs($muting_user);

        //あらかじめ1件登録
        $mute_user = MuteUser::create([
            'muting_user_id' => $muting_user->id,
            'user_id' => $muted_user->id,
        ]);

        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->alreadyMuteThisUser('ミュートユーザー');

        //重複登録で失敗
        $url = '/api/mute_users';
        $response = $this->json('PUT', $url, ['user_id' => $muted_user->id]);
        $response->assertStatus(422)->assertJsonFragment([
            'user_id' => [$expecting_message]
        ]);
    }




    /**
     * @test
     */
    public function ミュートユーザー削除成功(): void
    {
        //ユーザーをfactoryで作成
        $users = User::factory(2)->create();
        $muting_user = $users[0];
        $muted_user = $users[1];
        $this->actingAs($muting_user);

        //あらかじめ1件登録
        $mute_user = MuteUser::create([
            'muting_user_id' => $muting_user->id,
            'user_id' => $muted_user->id,
        ]);

        $url = '/api/mute_users';
        $response = $this->json('DELETE', $url, ['user_id' => $muted_user->id]);
        $response->assertStatus(200);

        $this->assertDatabaseMissing('mute_users', [
            'muting_user_id' => $muting_user->id,
            'user_id' => $muted_user->id,
        ]);
    }

    /**
     * @test
     * @dataProvider notDestroyMuteUserDataProvider
     */
    public function ミュートユーザー削除失敗「型関係」(string $key, $value): void
    {
        //ユーザーをfactoryで作成
        $users = User::factory(2)->create();
        $muting_user = $users[0];
        $muted_user = $users[1];
        $this->actingAs($muting_user);

        //あらかじめ1件登録
        $mute_user = MuteUser::create([
            'muting_user_id' => $muting_user->id,
            'user_id' => $muted_user->id,
        ]);

        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->cancel('ミュートユーザー');

        $url = '/api/mute_users';
        $response = $this->json('DELETE', $url, [$key => $value]);
        $response->assertStatus(422)->assertJsonFragment([
            'user_id' => [$expecting_message]
        ]);
    }

    /**
     * データプロバイダ(Destroy失敗)
     * [key,value]
     */
    public function notDestroyMuteUserDataProvider(): array
    {
        return [
            'ミュートユーザー登録(null)' => ['user_id', null],
            'ミュートユーザー登録(空文字)' => ['user_id', ''],
            'ミュートユーザー登録(文字列「null」)' => ['user_id', 'null'],
            'ミュートユーザー登録(文字列)' => ['user_id', 'aaa'],
        ];
    }
    /**
     * @test
     */
    public function ミュートユーザー削除失敗「自分」(): void
    {
        //ユーザーをfactoryで作成
        $users = User::factory(1)->create();
        $muting_user = $users[0];
        $this->actingAs($muting_user);

        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->cancel('ミュートユーザー');

        $url = '/api/mute_users';
        $response = $this->json('DELETE', $url, ['user_id' => $muting_user->id]);
        $response->assertStatus(422)->assertJsonFragment([
            'user_id' => [$expecting_message]
        ]);
    }
    /**
     * @test
     */
    public function ミュートユーザー削除失敗「そもそも未登録」(): void
    {
        //ユーザーをfactoryで作成
        $users = User::factory(2)->create();
        $muting_user = $users[0];
        $muted_user = $users[1];
        $this->actingAs($muting_user);

        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->notMuteThisUser('ミュートユーザー');

        //そもそも登録されていない
        $url = '/api/mute_users';
        $response = $this->json('DELETE', $url, ['user_id' => $muted_user->id]);
        $response->assertStatus(422)->assertJsonFragment([
            'user_id' => [$expecting_message]
        ]);
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
