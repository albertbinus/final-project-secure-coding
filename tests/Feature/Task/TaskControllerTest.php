<?php

namespace Tests\Feature\Task;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Firebase\JWT\JWT;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password123!'),
            'role' => 'admin'
        ]);
        // Generate JWT token
       $this->token = $this->generateToken($this->user);
   }
    private function generateToken($user)
   {
       $secretKey = env('JWT_KEY');
       $tokenId = base64_encode(random_bytes(16));
       $issuedAt = new \DateTimeImmutable();
       $expire = $issuedAt->modify('+60 minutes')->getTimestamp();
       
       $data = [
           'iat' => $issuedAt->getTimestamp(),
           'jti' => $tokenId,
           'iss' => "localhost",
           'nbf' => $issuedAt->getTimestamp(),
           'exp' => $expire,
           'data' => [
               'userID' => $user->id,
               'role' => $user->role
           ]
       ];
        return JWT::encode($data, $secretKey, 'HS512');
   }
    public function test_can_get_all_tasks()
   {
       // Create some test tasks
       Task::create([
           'user_id' => $this->user->id,
           'title' => 'Test Task 1',
           'description' => 'Test Description 1',
           'status' => 'pending',
           'due_date' => '2024-12-31',
           'priority' => 1
       ]);
        $response = $this->withHeaders([
           'Authorization' => 'Bearer ' . $this->token
       ])->getJson('/api/tasks');
        $response->assertStatus(200)
               ->assertJsonStructure([
                   'data' => [
                       '*' => [
                           'id',
                           'title',
                           'description',
                           'status',
                           'due_date',
                           'priority',
                           'created_at',
                           'updated_at'
                       ]
                   ]
               ]);
   }
    public function test_can_create_task()
   {
       $taskData = [
           'title' => 'New Task',
           'description' => 'Task Description',
           'status' => 'pending',
           'due_date' => '2024-12-31',
           'priority' => 2
       ];
        $response = $this->withHeaders([
           'Authorization' => 'Bearer ' . $this->token
       ])->postJson('/api/tasks', $taskData);
        $response->assertStatus(201)
               ->assertJsonStructure([
                   'data' => [
                       'id',
                       'title',
                       'description',
                       'status',
                       'due_date',
                       'priority',
                       'created_at',
                       'updated_at'
                   ]
               ]);
        $this->assertDatabaseHas('tasks', [
           'title' => 'New Task',
           'user_id' => $this->user->id
       ]);
   }
    public function test_can_show_task()
   {
       $task = Task::create([
           'user_id' => $this->user->id,
           'title' => 'Test Task',
           'description' => 'Test Description',
           'status' => 'pending',
           'due_date' => '2024-12-31',
           'priority' => 1
       ]);
        $response = $this->withHeaders([
           'Authorization' => 'Bearer ' . $this->token
       ])->getJson("/api/tasks/{$task->id}");
        $response->assertStatus(200)
               ->assertJsonStructure([
                   'data' => [
                       'id',
                       'title',
                       'description',
                       'status',
                       'due_date',
                       'priority',
                       'created_at',
                       'updated_at'
                   ]
               ]);
   }
    public function test_can_update_task()
   {
       $task = Task::create([
           'user_id' => $this->user->id,
           'title' => 'Test Task',
           'description' => 'Test Description',
           'status' => 'pending',
           'due_date' => '2024-12-31',
           'priority' => 1
       ]);
        $updatedData = [
           'title' => 'Updated Task',
           'description' => 'Updated Description',
           'status' => 'in_progress',
           'due_date' => '2024-12-31',
           'priority' => 2
       ];
        $response = $this->withHeaders([
           'Authorization' => 'Bearer ' . $this->token
       ])->putJson("/api/tasks/{$task->id}", $updatedData);
        $response->assertStatus(200)
               ->assertJsonStructure([
                   'data' => [
                       'id',
                       'title',
                       'description',
                       'status',
                       'due_date',
                       'priority',
                       'created_at',
                       'updated_at'
                   ]
               ]);
        $this->assertDatabaseHas('tasks', [
           'id' => $task->id,
           'title' => 'Updated Task',
           'status' => 'in_progress'
       ]);
   }
    public function test_can_delete_task()
   {
       $task = Task::create([
           'user_id' => $this->user->id,
           'title' => 'Test Task',
           'description' => 'Test Description',
           'status' => 'pending',
           'due_date' => '2024-12-31',
           'priority' => 1
       ]);
        $response = $this->withHeaders([
           'Authorization' => 'Bearer ' . $this->token
       ])->deleteJson("/api/tasks/{$task->id}");
        $response->assertStatus(204);
       $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
   }
    public function test_cannot_create_task_without_authentication()
   {
       $taskData = [
           'title' => 'New Task',
           'description' => 'Task Description',
           'status' => 'pending',
           'due_date' => '2024-12-31',
           'priority' => 2
       ];
        $response = $this->postJson('/api/tasks', $taskData);
       $response->assertStatus(401);
   }
    public function test_cannot_create_task_with_invalid_data()
   {
       $taskData = [
           'title' => '', // Empty title should fail validation
           'status' => 'invalid_status', // Invalid status should fail validation
           'priority' => 10 // Priority outside range should fail validation
       ];
        $response = $this->withHeaders([
           'Authorization' => 'Bearer ' . $this->token
       ])->postJson('/api/tasks', $taskData);
        $response->assertStatus(422)
               ->assertJsonValidationErrors(['title', 'status', 'priority']);
   }
}
