<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AuthControllerTest extends TestCase
{
    // use RefreshDatabase;
    protected $user;

    public function testLoginCheckNotLogin()
    {
        // GET リクエスト
        $response = $this->json('GET', 'api/check');
        $response->assertStatus(200);
        //返り値 Boolean false
        $this->assertEquals(false, $response);
    }
}
