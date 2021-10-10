<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GuestAuthControllerTest extends TestCase
{
    /**
     * @test
     * @dataProvider loginDataProvider
     */
    public function ゲストログイン成功：ユーザーidが1or2or3のときのみログイン【guestLogin】($user_id)
    {
        //ユーザー(role=guest)を3人作成
        $users = User::factory()->setRoleGuest()->count(3)->create();
        $user = $users[$user_id - 1];
        $url = 'api/login/guest';
        $response = $this->json('POST', $url, [
            'user_id' => $user_id,
        ]);
        $response->assertStatus(200)->assertJsonCount(2)
            ->assertJson([
                'message' => 'guest_login_success',
                'name' => $user->name,
            ]);
    }
    /**
     * データプロバイダ
     * [$user_id]
     */
    public function loginDataProvider(): array
    {
        return [
            'ユーザーid：1' => [1],
            'ユーザーid：2' => [2],
            'ユーザーid：3' => [3],
        ];
    }
    /**
     * @test
     * @dataProvider wLoginDataProvider
     */
    public function ゲストログイン成功：既にログインしている状態で同時ログインを許可【guestLogin】($user_id)
    {
        $users = User::factory()->setRoleGuest()->count(3)->create();
        $user = $users[$user_id - 1];
        //1回目ログイン
        $url = 'api/login/guest';
        $this->json('POST', $url, [
            'user_id' => $user_id,
        ]);
        //2回目ログイン
        $response = $this->json('POST', $url, [
            'user_id' => $user_id,
        ]);
        $response->assertStatus(200)->assertJsonCount(2)
            ->assertJson([
                'message' => 'guest_login_success',
                'name' => $user->name,
            ]);
    }
    /**
     * データプロバイダ
     * [$user_id]
     */
    public function wLoginDataProvider(): array
    {
        return [
            'ユーザーid：1' => [1],
            'ユーザーid：2' => [2],
            'ユーザーid：3' => [3],
        ];
    }
    /**
     * @test
     * @dataProvider notLoginDataProvider
     */
    public function ゲストログイン失敗：ユーザーidが1or2or3以外【guestLogin】($user_id)
    {
        //ユーザー(role=guest)を5人作成
        $users = User::factory()->setRoleGuest()->count(5)->create();
        $user = $users[$user_id - 1];
        $url = 'api/login/guest';
        $response = $this->json('POST', $url, [
            'user_id' => $user_id,
        ]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [$user_id]
     */
    public function notLoginDataProvider(): array
    {
        return [
            'ユーザーid：4' => [4],
            'ユーザーid：5' => [5],
        ];
    }
    /**
     * @test
     */
    public function ログイン失敗：既にゲストログインしている状態で別のゲストユーザーにログインしようとうする【guestLogin】()
    {
        $user_1 = User::factory()->setEmail('one@4tsuba.site')->setRoleGuest()->count(1)->create()->first();
        $user_2 = User::factory()->setEmail('two@4tsuba.site')->setRoleGuest()->count(1)->create()->first();
        $url = 'api/login/guest';

        $this->json('POST', $url, [
            'user_id' => $user_1->id,
        ]);
        //別のアカウントでログイン
        $response = $this->json('POST', $url, [
            'user_id' => $user_2->id,
        ]);

        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson([
                'message' => 'you_have_already_logged_in_another_account'
            ]);
    }
    /**
     * @test
     */
    public function ログイン失敗：既にログインしている状態で別のゲストユーザーにログインしようとうする【guestLogin】()
    {
        $user_1 = User::factory()->setEmail('one@4tsuba.site')->count(1)->create()->first();
        $user_2 = User::factory()->setEmail('two@4tsuba.site')->setRoleGuest()->count(1)->create()->first();
        //ログイン
        $this->json('POST', 'login', [
            'email' => $user_1->email,
            'password' => 'p@ssw0rd',
        ]);

        //ゲストユーザーでログイン
        $response = $this->json('POST', 'api/login/guest', [
            'user_id' => $user_2->id,
        ]);
        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson([
                'message' => 'you_have_already_logged_in_another_account'
            ]);
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
