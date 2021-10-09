<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Database\Seeders\UsersSeeder;

class AuthControllerTest extends TestCase
{
    //use RefreshDatabase;

    /** @test */
    public function ログインチェック：ログインしていない場合【CheckNotLoginOrNot】()
    {
        $response = $this->json('GET', '/api/check');
        $response->assertStatus(200);
        $this->assertFalse($response->original);
    }
    /** @test */
    public function ログインチェック：ログインしている場合【CheckLoginOrNot】()
    {
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        $response = $this->json('GET', 'api/check');
        $response->assertStatus(200);
        $this->assertTrue($response->original);
    }
    /** @test */
    public function 自分のユーザーid確認成功：ログインしている【returnMyId】()
    {
        $user = User::factory(10)->create()->pop();
        $this->actingAs($user);

        $response = $this->json('GET', 'api/users/me');
        $response->assertStatus(200);
        $this->assertSame($user->id, $response->original);
    }
    /** @test */
    public function 自分のユーザーid確認失敗：ログインしていない【returnMyId】()
    {
        $response = $this->json('GET', 'api/users/me');
        $response->assertStatus(401);
    }
    /** @test */
    public function 自分のユーザー情報確認成功：ログインしている【returnMyInfo】()
    {
        $user = User::factory(10)->create()->pop();
        $this->actingAs($user);

        $response = $this->json('GET', 'api/users/me/info');
        $response->assertStatus(200)
            ->assertJson(
                [
                    'email' => $user->email,
                    'icon_name' => 'no_image.png',
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => 'normal',
                ]
            );
    }
    /** @test */
    public function 自分のユーザー情報確認失敗：ログインしていない【returnMyInfo】()
    {
        $response = $this->json('GET', 'api/users/me/info');
        $response->assertStatus(401);
    }
    /**
     * @test
     * @dataProvider editProfileDataProvider
     */
    public function 表示プロフィール編集成功：【editProfile】($new_name, $new_icon)
    {
        $user = User::factory(10)->create()->pop();
        $this->actingAs($user);
        //フェイクのストレージを指定
        Storage::fake('local');

        $response = $this->json('POST', 'api/users/me/profile', ['name' => $new_name, 'icon' => $new_icon]);
        $response->assertStatus(200);
        //DB確認
        $this->assertDatabaseHas('users', [
            'name' => $new_name,
            'icon_name' => $new_icon->hashName(),
        ]);
        //ストレージ確認
        Storage::disk('local')->assertExists('public/icons/' . $new_icon->hashName());
    }
    /**
     * データプロバイダ
     * [key,value]
     */
    public function editProfileDataProvider(): array
    {
        $uploaded_image = UploadedFile::fake()->image('icon.jpg', 500, 500)->size(3000);

        return [
            '名前' => ['新しい名前', $uploaded_image],
            '名前(1文字)' => ['🤔', $uploaded_image],
            '名前(20文字)' => ['12345678901234567890', $uploaded_image],
        ];
    }

    /**
     * @test
     * @dataProvider notEditProfileDataProvider_1
     */
    public function 表示プロフィール編集失敗_名前：【editProfile】($new_name, $new_icon)
    {
        $user = User::factory(10)->create()->pop();
        $this->actingAs($user);
        //フェイクのストレージを指定
        Storage::fake('local');

        $response = $this->json('POST', 'api/users/me/profile', ['name' => $new_name, 'icon' => $new_icon]);
        $response->assertStatus(422);
        //DB確認
        $this->assertDatabaseMissing('users', [
            'name' => $new_name,
            'icon_name' => $new_icon->hashName(),
        ]);
        //ストレージ確認
        Storage::disk('local')->assertMissing('public/icons/' . $new_icon->hashName());
    }
    /**
     * データプロバイダ
     * [key,value]
     */
    public function notEditProfileDataProvider_1(): array
    {
        $uploaded_image = UploadedFile::fake()->image('icon.jpg', 500, 500)->size(3000);

        return [
            '名前(null)' => [null, $uploaded_image],
            '名前(文字列null)' => ['null', $uploaded_image],
            '名前(21文字)' => ['123456789012345678901', $uploaded_image],
            '名前(HTMLタグを含む)' => ['<h1>', $uploaded_image],
        ];
    }
    /**
     * @test
     * @dataProvider notEditProfileDataProvider_2
     */
    public function 表示プロフィール編集失敗_画像：【editProfile】($new_name, $new_icon)
    {
        $user = User::factory(10)->create()->pop();
        $this->actingAs($user);
        //フェイクのストレージを指定
        Storage::fake('local');

        $response = $this->json('POST', 'api/users/me/profile', ['name' => $new_name, 'icon' => $new_icon]);
        $response->assertStatus(422);
        //DB確認
        $this->assertDatabaseMissing('users', [
            'name' => $new_name,
            'icon_name' => $new_icon->hashName(),
        ]);
        //ストレージ確認
        Storage::disk('local')->assertMissing('public/icons/' . $new_icon->hashName());
    }
    /**
     * データプロバイダ
     * [key,value]
     */
    public function notEditProfileDataProvider_2(): array
    {
        $uploaded_image_1 = UploadedFile::fake()->image('icon.jpg', 500, 500)->size(3001);
        $uploaded_image_2 = UploadedFile::fake()->image('icon.svg', 500, 500)->size(1000);
        $uploaded_image_3 = UploadedFile::fake()->image('icon.psd', 500, 500)->size(1000);
        return [
            '画像サイズ3001' => ['新しい名前', $uploaded_image_1],
            '未対応画像mime' => ['新しい名前', $uploaded_image_2],
            'そもそも画像ファイルじゃない' => ['新しい名前', $uploaded_image_3],
        ];
    }
    /** @test */
    public function ゲストプロフィールリセット成功【resetGuestProfile】()
    {
        //ゲストユーザーをseedする
        $this->seed(UsersSeeder::class);
        //seedしたユーザーを取得
        $acting_user = User::where('id', 1)->first();
        $this->actingAs($acting_user);

        $response = $this->json('GET', 'api/users/me/profile');
        $response->assertStatus(200);
        //DB確認
        $this->assertDatabaseHas('users', [
            'name' => $acting_user->name,
            'icon_name' => $acting_user->icon_name,
        ]);
    }
    /** @test */
    public function ゲストプロフィールリセット失敗：normalユーザーのため【resetGuestProfile】()
    {
        //normalユーザーをfactoryする
        $user = User::factory(10)->create()->pop();
        $this->actingAs($user);

        $response = $this->json('GET', 'api/users/me/profile');
        $response->assertStatus(200);
        $this->assertSame('for_guest_only.', $response->original);
    }

    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
