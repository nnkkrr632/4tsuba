<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
//追記
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class TableDefinitionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testTableDefinition()
    {
        $this->assertTrue(
            Schema::hasColumns('users', [
                'id', 'created_at', 'updated_at', 'name', 'email', 'email_verified_at',
                'password', 'icon_name', 'icon_size', 'role', 'remember_token'
            ])
        );
        $this->assertTrue(
            Schema::hasColumns('images', [
                'id', 'created_at', 'updated_at', 'thread_id',
                'post_id', 'image_size', 'image_name'
            ])
        );
    }
    public function testStoreUsers()
    {
        $user = new User();
        $user->name = 'PHPユニットのテストユーザー2';
        $user->email = 'nn@test.com';
        $user->password = bcrypt('p@ssw0rd');
        $saveUser = $user->save();

        $this->assertTrue($saveUser);
    }
}
