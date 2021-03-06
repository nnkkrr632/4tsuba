<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Image;
use App\Models\Like;
use App\Models\MuteUser;
use App\Models\MuteWord;
use App\Models\Response;
use Database\Factories\PostFactory;
use Database\Factories\LikeFactory;
use Database\Factories\ResponseFactory;
use Database\Factories\ImageFactory;
use Database\Factories\MuteUserFactory;
use Database\Factories\MuteWordFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Database\Seeders\UsersSeeder;

class AuthControllerTest extends TestCase
{
    //use RefreshDatabase;

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
        $uploaded_image_1 = UploadedFile::fake()->image('icon.jpg', 500, 500)->size(3000);
        $uploaded_image_2 = UploadedFile::fake()->image('icon.jpeg', 500, 500)->size(3000);
        $uploaded_image_3 = UploadedFile::fake()->image('icon.png', 500, 500)->size(3000);
        $uploaded_image_4 = UploadedFile::fake()->image('icon.gif', 500, 500)->size(3000);

        return [
            '名前(1文字)' => ['🤔', $uploaded_image_1],
            '名前(20文字)' => ['12345678901234567890', $uploaded_image_1],
            '画像(3MBjpg)' => ['新しい名前', $uploaded_image_1],
            '画像(3MBjpeg)' => ['新しい名前', $uploaded_image_2],
            '画像(3MBpng)' => ['新しい名前', $uploaded_image_3],
            '画像(3MBgif)' => ['新しい名前', $uploaded_image_4],
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
            //nullにhashName()はエラー
            //'icon_name' => $new_icon->hashName(),
        ]);
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
            '画像(3MB以上)' => ['新しい名前', $uploaded_image_1],
            '画像(未対応画像mime)' => ['新しい名前', $uploaded_image_2],
            '画像(画像ファイルではない)' => ['新しい名前', $uploaded_image_3],
            '画像(null)' => ['新しい名前', null],
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
    /**
     * @test
     * @dataProvider editAccountDataProvider
     */
    public function アカウント編集成功【editAccount】($email, $current_password, $password, $password_confirm)
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $response = $this->json('PATCH', 'api/users/me', [
            'email' => $email,
            'current_password' => $current_password,
            'password' => $password,
            'password_confirm' => $password_confirm,
        ]);
        $response->assertStatus(200);
        //DB確認
        $this->assertDatabaseHas('users', [
            'email' => $email,
            // 'password' => bcrypt($password),
        ]);
    }
    /**
     * データプロバイダ
     * [key,value]
     */
    public function editAccountDataProvider(): array
    {
        return [
            '通常' => ['example@4tsuba.site', 'p@ssw0rd', 'aaaaaa12', 'aaaaaa12'],
            'メール50文字' => [str_repeat("a", 38) . '@4tsuba.site', 'p@ssw0rd', 'aaaaaa12', 'aaaaaa12'],
            'パスワード(8文字)' => ['example@4tsuba.site', 'p@ssw0rd', 'aaaaaa12', 'aaaaaa12'],
            'パスワード(24文字)' => ['example@4tsuba.site', 'p@ssw0rd', 'aaaaaaaaaaaaaaaaaaaaaa12', 'aaaaaaaaaaaaaaaaaaaaaa12']
        ];
    }
    /**
     * @test
     * @dataProvider notEditAccountDataProvider_1
     */
    public function アカウント編集失敗【editAccount】($email, $current_password, $password, $password_confirm)
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        //ゲストユーザーをseedする(emailを衝突させるため)
        $this->seed(UsersSeeder::class);

        $response = $this->json('PATCH', 'api/users/me', [
            'email' => $email,
            'current_password' => $current_password,
            'password' => $password,
            'password_confirm' => $password_confirm,
        ]);
        $response->assertStatus(422);
    }
    /**
     * データプロバイダ
     * [key,value]
     */
    public function notEditAccountDataProvider_1(): array
    {
        return [
            'メール(ユニーク制約)' => ['g_u_1@4tsuba.site', 'p@ssw0rd', 'aaaaaa12', 'aaaaaa12'],
            'メール(51文字)' => [str_repeat("a", 39) . '@4tsuba.site', 'p@ssw0rd', 'aaaaaa12', 'aaaaaa12'],
            'パスワード(7文字)' => ['example@4tsuba.site', 'p@ssw0rd', 'aaaaa12', 'aaaaa12'],
            'パスワード(25文字)' => ['example@4tsuba.site', 'p@ssw0rd', 'aaaaaaaaaaaaaaaaaaaaaaa12', 'aaaaaaaaaaaaaaaaaaaaaaa12'],
            'カレントパスワード(あってない)' => ['example@4tsuba.site', 'passw0rd', 'aaaaa12', 'aaaaa12'],
            'パスワード確認(一致してない)' => ['example@4tsuba.site', 'p@ssw0rd', 'aaaaa12', 'aaaaa13'],
            'パスワード(7文字)' => ['example@4tsuba.site', 'p@ssw0rd', 'aaaaa12', 'aaaaa12'],
        ];
    }
    /** @test */
    public function アカウント編集失敗：現在のパスワードが間違ってる【editAccount】()
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $response = $this->json('PATCH', 'api/users/me', [
            'email' => 'example@4tsuba.site',
            'current_password' => 'passw0rd',
            'password' => 'aaaaaa12',
            'password_confirm' => 'aaaaaa12',
        ]);
        $response->assertStatus(200);
        $this->assertSame('bad_password', $response->original);
    }
    /** @test */
    public function アカウント編集失敗：ゲストユーザー【editAccount】()
    {
        //ゲストユーザーをseedする
        $this->seed(UsersSeeder::class);
        //seedしたユーザーを取得
        $acting_user = User::where('id', 1)->first();
        $this->actingAs($acting_user);

        $response = $this->json('PATCH', 'api/users/me', [
            'email' => 'example@4tsuba.site',
            'current_password' => 'guestUser1',
            'password' => 'aaaaaa12',
            'password_confirm' => 'aaaaaa12',
        ]);
        $response->assertStatus(403);
    }

    /**
     * @test
     * @group m
     */
    public function アカウント削除成功【destroy】()
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        //フェイクのストレージを指定
        Storage::fake('local');
        //ストレージ保存
        $icon = UploadedFile::fake()->image('icon.jpg', 500, 500)->size(3000);
        $icon->storeAs('public/icons/', 'no_image.png', ['disk' => 'local']);
        //ストレージ確認
        Storage::disk('local')->assertExists('public/icons/no_image.png');

        $thread = Thread::factory()->setUserId($user->id)->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $post = Post::factory()->setUserId($user->id)->count(1)->create()->first();

        ImageFactory::initializePostId();
        $image = Image::factory()->count(1)->create()->first();
        //ストレージ確認
        Storage::disk('local')->assertExists('public/images/' . $image->image_name);

        LikeFactory::initializePostId();
        $like = Like::factory()->setUserId($user->id)->count(1)->create()->first();
        ResponseFactory::initializeOriginDPostId();
        $respon = Response::factory()->count(1)->create()->first();
        MuteUserFactory::initializeUserId();
        $mute_user = MuteUser::factory()->count(1)->create()->first();
        $mute_word = MuteWord::factory()->setUserId($user->id)->count(1)->create()->first();

        $response = $this->json('DELETE', 'api/users/me', [
            'password' => 'p@ssw0rd',
        ]);
        $response->assertStatus(200);
        //DB確認
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ])->assertDatabaseHas('threads', [
            'id' => $thread->id,
        ])->assertDatabaseHas('posts', [
            'id' => $post->id,
        ])->assertDatabaseHas('images', [
            'id' => $image->id,
        ])->assertDatabaseMissing('likes', [
            'id' => $like->id,
        ])->assertDatabaseHas('responses', [
            'id' => $respon->id,
        ])->assertDatabaseMissing('mute_users', [
            'id' => $mute_user->id,
        ])->assertDatabaseMissing('mute_words', [
            'id' => $mute_word->id,
        ]);

        //ストレージ確認(no_image.pngのときは消さない)
        Storage::disk('local')->assertExists('public/icons/no_image.png');
        //ストレージ確認
        Storage::disk('local')->assertExists('public/images/' . $image->image_name);
    }
    /** @test */
    public function アカウント削除失敗【destroy】()
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $response = $this->json('DELETE', 'api/users/me', [
            'password' => 'aaaaaa13',
        ]);
        $response->assertStatus(200);
        $this->assertSame('bad_password', $response->original);
    }
    /** @test */
    public function アカウント削除失敗：ゲストユーザー【destroy】()
    {
        //ゲストユーザーをseedする
        $this->seed(UsersSeeder::class);
        //seedしたユーザーを取得
        $acting_user = User::where('id', 1)->first();
        $this->actingAs($acting_user);

        $response = $this->json('DELETE', 'api/users/me', [
            'password' => 'guestUser1',
        ]);
        $response->assertStatus(403);
        //DB確認
        $this->assertDatabaseHas('users', [
            'id' => $acting_user->id,
        ]);
    }

    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('images')->truncate();
        DB::table('likes')->truncate();
        DB::table('responses')->truncate();
        DB::table('mute_users')->truncate();
        DB::table('mute_words')->truncate();
        DB::table('users')->truncate();
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
