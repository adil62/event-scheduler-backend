<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_successfully_registers_user()
    {
        $response = $this->post('/api/auth/register', [
            "firstName" => "test",
            "lastName" =>"test",
            "email" => "test@gmail.com",
            "password" => "12345678"
        ]);

        $response->assertJson ([
            "message" => 'user successfully registered.' ,
            "user" => [ 
                "first_name" => "test",
                "last_name" => "test",
                "email" => "test@gmail.com",
                "id"=> 1
            ]
        ]);
    }

    public function test_can_successfully_login()
    {
        $user = User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('12345678'),
         ]);
 
        $response = $this->post('/api/auth/login', [
            "email" => 'test@test.com',
            "password" => '12345678'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            "message",
            "access_token",
            "token_type",
            "expires_at"
        ]);
    }
}
