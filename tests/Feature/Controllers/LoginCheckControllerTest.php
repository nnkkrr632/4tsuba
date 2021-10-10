<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class LoginCheckControllerTest extends TestCase
{
    /** @test */
    public function ログインチェック：ログインしていない場合【CheckLoginOrNot】()
    {
        $response = $this->json('GET', '/api/check/login');
        $response->assertStatus(200);
        $this->assertFalse($response->original);
    }
    /** @test */
    public function ログインチェック：ログインしている場合【CheckLoginOrNot】()
    {
        $user = User::factory(1)->create()->first();
        $this->actingAs($user);

        $response = $this->json('GET', 'api/check/login');
        $response->assertStatus(200);
        $this->assertTrue($response->original);
    }
    public function teardown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        parent::tearDown();
    }
}
