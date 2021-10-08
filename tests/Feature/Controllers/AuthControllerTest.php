<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testLoginCheckNotLogin()
    {
        // GET リクエスト
        $response = $this->json('GET', '/api/check');
        $response->assertStatus(200);
        //返り値 Boolean false
        $this->assertFalse($response->original);
    }
    public function testLoginCheckLogin()
    {
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        // GET リクエスト
        $response = $this->json('GET', 'api/check');
        $response->assertStatus(200);
        //返り値 Boolean true
        $this->assertTrue($response->original);
    }
}
