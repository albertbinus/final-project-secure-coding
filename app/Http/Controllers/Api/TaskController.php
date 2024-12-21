<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Helpers\PublicHelper;

class TaskController extends Controller
{
    protected $publicHelper;
    public function __construct()
    {
        $this->publicHelper = new PublicHelper();
    }
    public function index()
    {
        $tasks = Task::paginate(15);
        return TaskResource::collection($tasks);
    }

    public function store(StoreTaskRequest $request)
    {
        // Dapatkan user ID dari JWT token
       $token = $this->publicHelper->getAndDecodeJWT();
       
       // Buat task baru
       $task = Task::create([
           'user_id' => $token->data->userID,
           'title' => $request->title,
           'description' => $request->description,
           'status' => $request->status,
           'due_date' => $request->due_date,
           'priority' => $request->priority
       ]);
        return new TaskResource($task);
    }

    public function show(Task $task)
    {
        return new TaskResource($task);
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());
        return new TaskResource($task);
    }

    public function destroy(Task $task)
    {
        $task->delete();
        return response()->json(null, 204);
    }
}
