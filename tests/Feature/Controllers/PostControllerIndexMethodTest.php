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
use App\Models\Response;
use App\Models\MuteUser;
use App\Models\MuteWord;
use Database\Factories\ImageFactory;
use Database\Factories\LikeFactory;
use Database\Factories\PostFactory;
use Database\Factories\MuteUserFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Database\Factories\ResponseFactory;
use Faker\Provider\DateTime;

class PostControllerIndexMethodTest extends TestCase
{
    /**
     * @test
     */
    public function ポスト取得：スレッド個別「スレッドの書込が取得できること」(): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->count(10)->create();
        ImageFactory::initializePostId();
        $images = Image::factory()->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'thread_id', 'value' => 1]);
        $response->assertStatus(200)->assertJsonCount(10);
        $array = $response->json();
        $posts_displayed_post_id_list = array_column($array, 'displayed_post_id');
        $posts_thread_id_list = array_column($array, 'thread_id');
        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], $posts_displayed_post_id_list);
        $this->assertSame([1, 1, 1, 1, 1, 1, 1, 1, 1, 1], $posts_thread_id_list);
    }
    /**
     * @test
     */
    public function ポスト取得：スレッド個別「取得した書込にいいね数と自分のいいねが反映されていること」(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->count(10)->create();
        LikeFactory::initializePostId();
        $likes = Like::factory()->count(5)->create();
        LikeFactory::initializePostId();
        $another_likes = Like::factory()->setUserId($another_user->id)->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'thread_id', 'value' => 1]);
        $response->assertStatus(200)->assertJsonCount(10);
        $array = $response->json();
        $posts_likes_count_list = array_column($array, 'likes_count');
        $posts_login_user_liked_list = array_column($array, 'login_user_liked');
        $this->assertSame([2, 2, 2, 2, 2, 0, 0, 0, 0, 0], $posts_likes_count_list);
        $this->assertSame([1, 1, 1, 1, 1, 0, 0, 0, 0, 0], $posts_login_user_liked_list);
    }
    /**
     * @test
     */
    public function ポスト取得：スレッド個別「ミュートワード含む書込がマスクされていること」(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        $mute_word = MuteWord::factory()->setUserId($user->id)->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $posts_with_mute_word = Post::factory()->state([
            'body' => $mute_word->mute_word,
        ])->count(5)->create();
        $posts = Post::factory()->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'thread_id', 'value' => 1]);
        $response->assertStatus(200)->assertJsonCount(10);
        $array = $response->json();
        $posts_has_mute_words_list = array_column($array, 'has_mute_words');
        $this->assertSame([true, true, true, true, true, false, false, false, false, false], $posts_has_mute_words_list);
    }
    /**
     * @test
     */
    public function ポスト取得：スレッド個別「ミュートユーザーの書込がマスクされていること」(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        MuteUserFactory::initializeUserId();
        $mute_user = MuteUser::factory()->state([
            'muting_user_id' => $user->id,
            'user_id' => $another_user->id,
        ])->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $posts_by_mute_user = Post::factory()->setUserId($another_user->id)->count(5)->create();
        $posts = Post::factory()->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'thread_id', 'value' => 1]);
        $response->assertStatus(200)->assertJsonCount(10);
        $array = $response->json();
        $posts_posted_by_mute_users_list = array_column($array, 'posted_by_mute_users');
        $this->assertSame([true, true, true, true, true, false, false, false, false, false], $posts_posted_by_mute_users_list);
    }
    /**
     * @test
     */
    public function ポスト取得：スレッド個別「編集済みの書込が編集済みになっていること」(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $edited_posts = Post::factory()->state([
            'is_edited' => 1,
        ])->count(5)->create();
        $posts = Post::factory()->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'thread_id', 'value' => 1]);
        $response->assertStatus(200)->assertJsonCount(10);
        $array = $response->json();
        $edited_posts_list = array_column($array, 'is_edited');
        $this->assertSame([1, 1, 1, 1, 1, 0, 0, 0, 0, 0], $edited_posts_list);
    }
    /**
     * @test
     */
    public function ポスト取得：スレッド個別「削除済みの書込が削除済みになっていること」(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $deleted_posts = Post::factory()->state([
            'deleted_at' => DateTime::dateTimeThisDecade(),
        ])->count(5)->create();
        $posts = Post::factory()->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'thread_id', 'value' => 1]);
        $response->assertStatus(200)->assertJsonCount(10);
        $array = $response->json();
        $deleted_posts_list = array_column($array, 'deleted_at');
        $this->assertSame([
            $deleted_posts[0]->deleted_at, $deleted_posts[1]->deleted_at, $deleted_posts[2]->deleted_at,
            $deleted_posts[3]->deleted_at, $deleted_posts[4]->deleted_at, null, null, null, null, null
        ], $deleted_posts_list);
    }














    /**
     * @test
     */
    public function ポスト取得：スレッド返信「返信関係の書込のみを取得できること」(): void
    {
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->count(10)->create();
        ImageFactory::initializePostId();
        $images = Image::factory()->count(5)->create();
        ResponseFactory::initializeOriginDPostId();
        $responses = Response::factory()->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'responses', 'value' => [1, 1]]);
        $response->assertStatus(200)->assertJsonCount(5);
        $array = $response->json();
        $posts_thread_id_list = array_column($array, 'thread_id');
        $posts_displayed_post_id_list = array_column($array, 'displayed_post_id');
        $this->assertSame([1, 1, 1, 1, 1], $posts_thread_id_list);
        $this->assertSame([1, 2, 3, 4, 5], $posts_displayed_post_id_list);
        //$arrayは型が「array」だからコレクションと違って->responded_countができない。
        $this->assertSame(5, $array[0]['responded_count']);
    }










    /**
     * @test
     */
    public function ポスト取得：プロフィールページ書込欄「プロフィールユーザーの書込のみを取得できること」(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->setUserId($user->id)->count(5)->create();
        $another_posts = Post::factory()->setUserId($another_user->id)->count(5)->create();
        ImageFactory::initializePostId();
        $images = Image::factory()->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'user_id', 'value' => $another_user->id]);
        $response->assertStatus(200)->assertJsonCount(5);
        $array = $response->json();
        $posts_user_id_list = array_column($array, 'user_id');
        $this->assertSame([2, 2, 2, 2, 2], $posts_user_id_list);
    }

    /**
     * @test
     */
    public function ポスト取得：プロフィールページいいね欄「プロフィールユーザーがいいねした書込のみを取得できること」(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->setUserId($user->id)->count(5)->create();
        $another_posts = Post::factory()->setUserId($another_user->id)->count(5)->create();
        ImageFactory::initializePostId();
        $images = Image::factory()->count(5)->create();
        LikeFactory::initializePostId();
        $likes = Like::factory()->setUserId($another_user->id)->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'user_like', 'value' => $another_user->id]);
        $response->assertStatus(200)->assertJsonCount(5);
        $array = $response->json();
        $posts_liking_user_id_list = array_column($array, 'liking_user_id');
        $this->assertSame([2, 2, 2, 2, 2], $posts_liking_user_id_list);
    }









    /**
     * @test
     */
    public function ポスト取得：ワード検索「検索ワードを含む書込のみを取得できること」(): void
    {
        $users = User::factory()->count(2)->create();
        $user = $users[0];
        $another_user = $users[1];
        $this->actingAs($user);
        $thread = Thread::factory()->count(1)->create()->first();
        $search_word_list = ['検索ワード1', '検索ワード2'];
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->state([
            'body' => $search_word_list[0],
        ])->count(5)->create();
        $another_posts = Post::factory()->state([
            'body' => $search_word_list[1],
        ])->count(5)->create();
        ImageFactory::initializePostId();
        $images = Image::factory()->count(5)->create();

        $url = '/api/posts';
        $response = $this->json('GET', $url, ['where' => 'search', 'value' => $search_word_list]);
        $response->assertStatus(200)->assertJsonCount(10);
        $array = $response->json();
        $posts_body_list = array_column($array, 'body');
        $this->assertSame([
            $search_word_list[0], $search_word_list[0], $search_word_list[0], $search_word_list[0], $search_word_list[0],
            $search_word_list[1], $search_word_list[1], $search_word_list[1], $search_word_list[1], $search_word_list[1]
        ], $posts_body_list);
    }


    /**
     * データプロバイダ
     * [$thread_id, $body]
     */
    public function storePostDataProvider_1(): array
    {
        return [
            'スレッドid(1)' => [1, '書込'],
            '書込(200文字)' => [1, str_repeat("a", 200)],
        ];
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('images')->truncate();
        DB::table('responses')->truncate();
        DB::table('likes')->truncate();
        DB::table('mute_users')->truncate();
        DB::table('mute_words')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
