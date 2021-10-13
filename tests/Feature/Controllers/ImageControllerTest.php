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
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageControllerTest extends TestCase
{
    /** @test */
    public function LightBoxスレッド内画像()
    {
        //ユーザーをfactoryで作成
        $user = User::factory()->count(1)->create()->first();
        $this->actingAs($user);

        $thread = Thread::factory()->count(1)->create()->first();
        $posts = Post::factory()->count(3)->create();
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
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::table('threads')->truncate();
        DB::table('posts')->truncate();
        DB::table('images')->truncate();
        DB::table('responses')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
