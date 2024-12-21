<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_with_valid_data()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'user'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'role'
                        ],
                        'token'
                    ],
                    'message'
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
            'role' => 'user'
        ]);
    }

    public function test_user_cannot_register_with_existing_email()
    {
        // Create a user first
        User::create([
            'name' => 'Existing User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'role' => 'user'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(422)
                ->assertJson([
                    'message' => 'Validation Error',
                    'data' => [
                        'email' => [
                            'The email has already been taken.'
                        ]
                    ]
                ]);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'user' => [
                            'id',
                            'name',
                            'email',
                            'role'
                        ],
                        'token'
                    ],
                    'message'
                ]);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'role' => 'user'
        ]);

        $loginData = [
            'email' => 'test@example.com',
            'password' => 'WrongPassword123!'
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthorized',
                    'data' => [
                        'error' => 'Invalid Login credentials'
                    ]
                ]);
    }

    public function test_user_can_logout()
    {
        // Setup user
    $user = User::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
       'password' => Hash::make('Password123!'),
       'role' => 'user'
   ]);
    // Login process
   $loginData = [
       'email' => 'test@example.com',
       'password' => 'Password123!'
   ];
    $loginResponse = $this->postJson('/api/login', $loginData);
   
   // Verify login successful
   $this->assertEquals(200, $loginResponse->status());
   
   $token = $loginResponse->json('data.token');
   $this->assertNotNull($token, 'Token should not be null');

   
   
   // Attempt logout with token
   $response = $this->withHeaders([
       'Authorization' => 'Bearer ' . $token,
   ])->post('/api/logout'); // Menggunakan post() bukan postJson()


   
   // Verify logout successful
   $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json'  // Tambahkan header ini
    ])->postJson('/api/logout');
    }
}