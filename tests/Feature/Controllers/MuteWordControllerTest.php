<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\MuteWord;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class MuteWordControllerTest extends TestCase
{
    // テストデータのリセット
    //use RefreshDatabase;
    /** @test */
    public function testIndex()
    {
        //ユーザーをseederで作成
        $user = User::where('id', 4)->first();
        $this->actingAs($user);

        // テストデータをFactoryで作成
        //MuteWord::factory(2)->create();

        $response = $this->json('GET', '/api/mute_words');
        $response->assertStatus(200)->assertJsonCount(2)
            ->assertJson(
                [
                    [
                        'id' => 17,
                        'user_id' => 4,
                        'mute_word' => 'czsxt',
                    ],
                    [
                        'id' => 13,
                        'user_id' => 4,
                        'mute_word' => 'bfsuf',
                    ]
                ]
            );
    }
}
