<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\FormRequestMessage;

class CookieAuthenticationControllerTest extends TestCase
{
    /**
     * @test
     */
    public function ログイン成功【login】()
    {
        $user = User::factory()->setEmail('example@4tsuba.site')->count(1)->create()->first();

        $response = $this->json('POST', 'login', [
            'email' => $user->email,
            'password' => 'p@ssw0rd',
        ]);
        $response->assertStatus(200)->assertJsonCount(2)
            ->assertJson([
                'message' => 'login_success',
                'name' => $user->name,
            ]);
    }
    /**
     * @test
     */
    public function ログイン成功：既にログインしている状態で同時ログインを許可【login】()
    {
        $user = User::factory()->setEmail('example@4tsuba.site')->count(1)->create()->first();
        //1回目ログイン
        $this->json('POST', 'login', [
            'email' => $user->email,
            'password' => 'p@ssw0rd',
        ]);

        //2回目ログイン
        $response = $this->json('POST', 'login', [
            'email' => $user->email,
            'password' => 'p@ssw0rd',
        ]);
        $response->assertStatus(200)->assertJsonCount(2)
            ->assertJson([
                'message' => 'login_success',
                'name' => $user->name,
            ]);
    }
    /**
     * @test
     */
    public function ログイン失敗：既にログインしている状態で別のユーザーにログインしようとうする【login】()
    {
        $user_1 = User::factory()->setEmail('one@4tsuba.site')->count(1)->create()->first();
        $user_2 = User::factory()->setEmail('two@4tsuba.site')->count(1)->create()->first();
        //ログイン
        $this->json('POST', 'login', [
            'email' => $user_1->email,
            'password' => 'p@ssw0rd',
        ]);

        //別のアカウントでログイン
        $response = $this->json('POST', 'login', [
            'email' => $user_2->email,
            'password' => 'p@ssw0rd',
        ]);
        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson([
                'message' => 'you_have_already_logged_in_another_account'
            ]);
    }
    /**
     * @test
     */
    public function ログイン失敗：既にゲストログインしている状態で別のユーザーにログインしようとうする【login】()
    {
        $user_1 = User::factory()->setEmail('one@4tsuba.site')->setRoleGuest()->count(1)->create()->first();
        $user_2 = User::factory()->setEmail('two@4tsuba.site')->count(1)->create()->first();
        $url = 'api/login/guest';
        //ゲストログイン
        $this->json('POST', $url, [
            'user_id' => $user_1->id,
        ]);
        //別の一般ユーザーでログイン
        $response = $this->json('POST', 'login', [
            'email' => $user_2->email,
            'password' => 'p@ssw0rd',
        ]);

        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson([
                'message' => 'you_have_already_logged_in_another_account'
            ]);
    }
    /**
     * @test
     * @dataProvider notLoginDataProvider
     */
    public function ログイン失敗「型関係」と存在しないメールアドレス【login】($email, $password)
    {
        $user = User::factory()->setEmail('example@4tsuba.site')->count(1)->create()->first();

        $response = $this->json('POST', 'login', [
            'email' => $email,
            'password' => $password
        ]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [$email, $password]
     */
    public function notLoginDataProvider(): array
    {
        return [
            'メール(51文字)' => [str_repeat("a", 39) . '@4tsuba.site', 'p@ssw0rd'],
            'メール(null)' => [null, 'p@ssw0rd'],
            'メール(空文字)' => ['', 'p@ssw0rd'],
            'メール(文字列null)' => ['null', 'p@ssw0rd'],
            'メール(未登録)' => ['aaaaa@4tsuba.site', 'p@ssw0rd'],
            'パスワード(null)' => ['example@4tsuba.site', null],
            'パスワード(空文字)' => ['example@4tsuba.site', ''],
            'パスワード(文字列null)' => ['example@4tsuba.site', 'null'],
            'パスワード(7文字)' => ['example@4tsuba.site', '123456a'],
            'パスワード(25文字)' => ['example@4tsuba.site', '123456789012345678901234a']
        ];
    }
    /**
     * @test
     */
    public function ログイン失敗：パスワードが違う【login】()
    {
        $user = User::factory()->setEmail('example@4tsuba.site')->count(1)->create()->first();

        $response = $this->json('POST', 'login', [
            'email' => $user->email,
            'password' => 'aaaaaa12'
        ]);
        $response->assertStatus(422)->assertJsonFragment(
            ['email_password' => ['メールアドレスもしくはパスワードが正しくありません。']]
        );
    }
    /**
     * @test
     */
    public function ログアウト成功【logout】()
    {
        $user = User::factory()->setEmail('example@4tsuba.site')->count(1)->create()->first();
        //あらかじめログイン
        $this->json('POST', 'login', [
            'email' => $user->email,
            'password' => 'p@ssw0rd',
        ]);
        $response = $this->json('POST', 'logout');
        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson([
                'message' => 'logout_success',
            ]);
    }
    /**
     * @test
     */
    public function ログアウト：そもそもログインしていない【logout】()
    {
        $response = $this->json('POST', 'logout');
        $response->assertStatus(200)->assertJsonCount(1)
            ->assertJson([
                'message' => 'you_are_not_login',
            ]);
    }
    /**
     * @test
     * @dataProvider registerDataProvider
     */
    public function ユーザー登録成功【register】($name, $email, $password, $password_confirm)
    {
        $response = $this->json('POST', 'register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirm' => $password_confirm,
        ]);
        $response->assertStatus(200);
        //DB確認
        $this->assertDatabaseHas('users', [
            'name' => $name,
            'email' => $email,
            //passwordはbcryptするたび違う値になるから検証できない
        ]);
    }
    /**
     * データプロバイダ
     * [$name, $email, $password, $password_confirm]
     */
    public function registerDataProvider(): array
    {
        return [
            '表示名(1文字)' => ['🤔', 'example@4tsuba.site', 'p@ssw0rd', 'p@ssw0rd'],
            '表示名(20文字)' => ['12345678901234567890', 'example@4tsuba.site', 'p@ssw0rd', 'p@ssw0rd'],
            'メール(50文字)' => ['表示名', str_repeat("a", 38) . '@4tsuba.site', 'p@ssw0rd', 'p@ssw0rd'],
            'パスワード(8文字)' => ['表示名', 'example@4tsuba.site', '1234567a', '1234567a'],
            'パスワード(24文字)' => ['表示名', 'example@4tsuba.site', '12345678901234567890123a', '12345678901234567890123a']
        ];
    }
    /**
     * @test
     * @dataProvider notRegisterDataProvider
     */
    public function ユーザー登録失敗「型関係」【register】($name, $email, $password, $password_confirm)
    {
        $response = $this->json('POST', 'register', [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirm' => $password_confirm,
        ]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [$name, $email, $password, $password_confirm]
     */
    public function notRegisterDataProvider(): array
    {
        return [
            '表示名(null)' => [null, 'example@4tsuba.site', 'p@ssw0rd', 'p@ssw0rd'],
            '表示名(文字列null)' => ['null', 'example@4tsuba.site', 'p@ssw0rd', 'p@ssw0rd'],
            '表示名(21文字)' => ['123456789012345678901', 'example@4tsuba.site', 'p@ssw0rd', 'p@ssw0rd'],
            '表示名(HTMLタグを含む)' => ['<h1>aaa</h1>', 'example@4tsuba.site', 'p@ssw0rd', 'p@ssw0rd'],
            'メール(null)' => ['表示名', null, 'p@ssw0rd', 'p@ssw0rd'],
            'メール(文字列null)' => ['表示名', 'null', 'p@ssw0rd', 'p@ssw0rd'],
            'メール(51文字)' => ['表示名', str_repeat("a", 39) . '@4tsuba.site', 'p@ssw0rd', 'p@ssw0rd'],
            'パスワード(null)' => ['表示名', null, 'p@ssw0rd', 'p@ssw0rd'],
            'パスワード(文字列null)' => ['表示名', 'null', 'p@ssw0rd', 'p@ssw0rd'],
            'パスワード(7文字)' => ['表示名', 'example@4tsuba.site', '123456a', '123456a'],
            'パスワード(25文字)' => ['表示名', 'example@4tsuba.site', '123456789012345678901234a', '123456789012345678901234a'],
            'パスワード確認(null)' => ['表示名', null, 'p@ssw0rd', null],
            'パスワード確認(文字列null)' => ['表示名', 'null', 'p@ssw0rd', 'null'],
            'パスワード確認(不一致)' => ['表示名', 'example@4tsuba.site', '123456a', '123456b'],
        ];
    }
    /**
     * @test
     */
    public function ユーザー登録失敗：既に登録済みEmail【register】()
    {
        //衝突用emailあらかじめ1件登録
        $user = User::factory()->setEmail('conflict@4tsuba.site')->count(1)->create()->first();
        $form_request_message = new FormRequestMessage();
        $expecting_message = $form_request_message->emailAlreadyRegistered('メールアドレス');

        $response = $this->json('POST', 'register', [
            'name' => '衝突する男',
            'email' => $user->email,
            'password' => 'aaaaaa12',
            'password_confirm' => 'aaaaaa12',
        ]);
        $response->assertStatus(422)->assertJsonFragment([
            'email' => [$expecting_message]
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
