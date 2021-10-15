<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Thread;
use App\Models\Post;
use App\Models\Image;
use App\Models\Response;
use App\Models\Like;
use Database\Factories\PostFactory;
use Database\Factories\ImageFactory;
use Database\Factories\LikeFactory;
use Database\Factories\ResponseFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\assertEquals;

class ImageControllerTest extends TestCase
{
    /**
     * @test
     */
    public function LightBoxスレッド内画像()
    {
        //ユーザーをfactoryで作成
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $thread = Thread::factory()->count(1)->create()->first();
        $posts = Post::factory()->count(3)->create();
        ImageFactory::initializePostId();
        $images = Image::factory()->count(3)->create();

        $url = '/api/images/threads/' . $thread->id;
        $response = $this->json('GET', $url);
        $response->assertStatus(200)->assertJsonCount(3);
        $response->assertJson(
            [
                [
                    'displayed_post_id' => $posts[0]->displayed_post_id,
                    'post_id' => $posts[0]->id,
                    'thumb' => '/storage/images/' . $images[0]->image_name,
                    'src'  => '/storage/images/' . $images[0]->image_name,
                ],
                [
                    'displayed_post_id' => $posts[1]->displayed_post_id,
                    'post_id' => $posts[1]->id,
                    'thumb' => '/storage/images/' . $images[1]->image_name,
                    'src'  => '/storage/images/' . $images[1]->image_name,
                ],
                [
                    'displayed_post_id' => $posts[2]->displayed_post_id,
                    'post_id' => $posts[2]->id,
                    'thumb' => '/storage/images/' . $images[2]->image_name,
                    'src'  => '/storage/images/' . $images[2]->image_name,
                ],
            ]
        );
    }
    /**
     * @test
     */
    public function LightBox返信内画像()
    {
        //ユーザーをfactoryで作成
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $thread = Thread::factory()->count(1)->create()->first();
        //ポストのdisplayed_post_id初期化
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->count(3)->create();
        //画像のpost_id初期化
        ImageFactory::initializePostId();
        $images = Image::factory()->count(2)->create();
        ResponseFactory::initializeOriginDPostId();
        $responses = Response::factory()->count(3)->create();


        $url = '/api/images/threads/' . $thread->id . '/responses/' . $posts[0]->id;
        $response = $this->json('GET', $url);
        $response->assertStatus(200)->assertJsonCount(2);
        $response->assertJson(
            [
                [
                    'displayed_post_id' => $posts[0]->displayed_post_id,
                    'post_id' => $posts[0]->id,
                    'thumb' => '/storage/images/' . $images[0]->image_name,
                    'src'  => '/storage/images/' . $images[0]->image_name,
                ],
                [
                    'displayed_post_id' => $posts[1]->displayed_post_id,
                    'post_id' => $posts[1]->id,
                    'thumb' => '/storage/images/' . $images[1]->image_name,
                    'src'  => '/storage/images/' . $images[1]->image_name,
                ],

            ]
        );
    }
    /**
     * @test
     */
    public function LightBoxプローフィールページポスト内画像()
    {
        //ユーザーをfactoryで作成
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $thread = Thread::factory()->count(1)->create()->first();
        //ポストのdisplayed_post_id初期化
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->setUserId($user->id)->count(3)->create();
        //画像のpost_id初期化
        ImageFactory::initializePostId();
        $images = Image::factory()->count(2)->create();


        $url = '/api/images/users/' . $user->id . '/post';
        $response = $this->json('GET', $url);
        $response->assertStatus(200)->assertJsonCount(2);
        $response->assertJson(
            [
                [
                    'displayed_post_id' => $posts[1]->displayed_post_id,
                    'post_id' => $posts[1]->id,
                    'thumb' => '/storage/images/' . $images[1]->image_name,
                    'src'  => '/storage/images/' . $images[1]->image_name,
                ],
                [
                    'displayed_post_id' => $posts[0]->displayed_post_id,
                    'post_id' => $posts[0]->id,
                    'thumb' => '/storage/images/' . $images[0]->image_name,
                    'src'  => '/storage/images/' . $images[0]->image_name,
                ],

            ]
        );
    }
    /**
     * @test
     */
    public function LightBoxプローフィールページいいね内画像()
    {
        //ユーザーをfactoryで作成
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $thread = Thread::factory()->count(1)->create()->first();
        //ポストのdisplayed_post_id初期化
        PostFactory::initializeDisplayedPostId();
        $posts = Post::factory()->setUserId($user->id)->count(3)->create();
        //画像のpost_id初期化
        ImageFactory::initializePostId();
        $images = Image::factory()->count(2)->create();
        LikeFactory::initializePostId();
        $first_like = Like::factory()->count(1)->create();
        $second_like = Like::factory()->count(1)->create();

        $url = '/api/images/users/' . $user->id . '/like';
        $response = $this->json('GET', $url);
        $response->assertStatus(200)->assertJsonCount(2);
        $response->assertJson(
            [
                [
                    'displayed_post_id' => $posts[0]->displayed_post_id,
                    'post_id' => $posts[0]->id,
                    'thumb' => '/storage/images/' . $images[0]->image_name,
                    'src'  => '/storage/images/' . $images[0]->image_name,
                ],
                [
                    'displayed_post_id' => $posts[1]->displayed_post_id,
                    'post_id' => $posts[1]->id,
                    'thumb' => '/storage/images/' . $images[1]->image_name,
                    'src'  => '/storage/images/' . $images[1]->image_name,
                ],

            ]
        );
    }
    /**
     * @test
     * @group miss
     */
    public function LightBoxワード検索内画像()
    {
        //ユーザーをfactoryで作成
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $thread = Thread::factory()->count(1)->create()->first();
        //ポストのdisplayed_post_id初期化
        PostFactory::initializeDisplayedPostId();
        $search_word_list = ['ワード', '別'];
        $posts = Post::factory()->state([
            'body' => $search_word_list[0],
        ])->count(2)->create();
        $another_posts = Post::factory()->state([
            'body' => $search_word_list[1],
        ])->count(2)->create();

        //画像のpost_id初期化
        ImageFactory::initializePostId();
        $images = Image::factory()->count(4)->create();

        $url = '/api/images/search/';
        $response = $this->json('GET', $url, ['unique_word_list' => $search_word_list]);
        $response->assertStatus(200)->assertJsonCount(4);
        $response->assertJson(
            [
                [
                    'displayed_post_id' => $posts[0]->displayed_post_id,
                    'post_id' => $posts[0]->id,
                    'thumb' => '/storage/images/' . $images[0]->image_name,
                    'src'  => '/storage/images/' . $images[0]->image_name,
                ],
                [
                    'displayed_post_id' => $posts[1]->displayed_post_id,
                    'post_id' => $posts[1]->id,
                    'thumb' => '/storage/images/' . $images[1]->image_name,
                    'src'  => '/storage/images/' . $images[1]->image_name,
                ],
                [
                    'displayed_post_id' => $another_posts[0]->displayed_post_id,
                    'post_id' => $another_posts[0]->id,
                    'thumb' => '/storage/images/' . $images[2]->image_name,
                    'src'  => '/storage/images/' . $images[2]->image_name,
                ],
                [
                    'displayed_post_id' => $another_posts[1]->displayed_post_id,
                    'post_id' => $another_posts[1]->id,
                    'thumb' => '/storage/images/' . $images[3]->image_name,
                    'src'  => '/storage/images/' . $images[3]->image_name,
                ],
            ]
        );
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
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
