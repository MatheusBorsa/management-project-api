<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Utils\ApiResponseUtil;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;


class TaskController extends Controller
{   
    private function checkTaskPermission($taskOrClient, $user, array $allowedRoles = ['owner', 'participant'])
    {
        if ($taskOrClient instanceof Client) {
            $client = $taskOrClient;
        } else {
            $client = $taskOrClient->client;
        }
        
        $pivot = $client->users->firstWhere('id', $user->id)?->pivot;

        if (!$pivot || !in_array($pivot->role, $allowedRoles)) {
            return false;
        }
        return true;
    }

    public function createTask(Request $request, $clientId)
    {
        try {
            $currentUser = $request->user();
            $client = Client::with('users')->findOrFail($clientId);

            if (!$this->checkTaskPermission($client, $currentUser)) {
                return ApiResponseUtil::error(
                    'You are not authorized',
                    null,
                    403
                );
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'deadline' => 'nullable|date',
                'status' => 'required|string|in:' . implode(',', array_column(TaskStatus::cases(), 'value')),
                'assigned_to' => 'nullable|integer|exists:users,id'
            ]);

            $task = $client->tasks()->create($validated);

            return ApiResponseUtil::success(
                'Task created successfully',
                $task,
                201
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Failed to create task',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function updateTask(Request $request, $id)
    {
        try {
            $currentUser = $request->user();

            $task = Task::with('client.users')->findOrFail($id);

            if (!$this->checkTaskPermission($task, $currentUser)) {
                return ApiResponseUtil::error(
                    'You are not authorized',
                    null,
                    403
                );
            }
            
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'deadline' => 'sometimes|date',
                'status' => 'sometimes|in:' . implode(',', array_column(TaskStatus::cases(), 'value')),
                'assigned_to' => 'sometimes|nullable|exists:users,id'
            ]);

            $task->update($validated);

            return ApiResponseUtil::success(
                'Task updated successfully',
                new TaskResource($task),
                200
            );

        } catch (ValidationException $e) {
            return ApiResponseUtil::error(
                'Validation Failed',
                ['error' => $e->getMessage()],
                422
            );

        } catch (ModelNotFoundException $e) {
            return ApiResponseUtil::error(
                'Task not found',
                ['error' => $e->getMessage()],
                404
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Failed to update task',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function showTask(Request $request, $id)
    {
        try {
            $user = $request->user();

            $task = Task::with('client.users', 'assignedUser')->findOrFail($id);

            $pivot = $task->client->users->firstWhere('id', $user->id)?->pivot;
            if (!$pivot) {
                return ApiResponseUtil::error(
                    'You are not authorized',
                    null,
                    403
                );
            }

            return ApiResponseUtil::success(
                'Task retrieved successfully',
                new TaskResource($task),
                200
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Failed to retrieve task',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

    public function deleteTask(Request $request, $id)
    {
        try {
            $currentUser = $request->user();

            $task = Task::with('client.users')->findOrFail($id);

            if (!$this->checkTaskPermission($task, $currentUser)) {
                return ApiResponseUtil::error(
                    'You are not authorized',
                    null,
                    403
                );
            }

            $task->delete();

            return ApiResponseUtil::success(
                'Task removed successfully',
                null,
                200
            );
            
        } catch (ModelNotFoundException $e) {
            return ApiResponseUtil::error(
                'Task not found',
                ['error' => $e->getMessage()],
                404
            );

        } catch (Exception $e) {
            return ApiResponseUtil::error(
                'Failed to remove task',
                ['error' => $e->getMessage()],
                500
            );
        }
    }
}
