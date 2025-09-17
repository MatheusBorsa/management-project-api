<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\ApiResponseUtil;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return ApiResponseUtil::success(
            'Dashboard data',
            [
                'my_clients' => $user->clients()->withCount('tasks')->get(),
                'my_tasks' => Task::where('assigned_to', $user->id)
                                ->with('client')
                                ->orderBy('deadline')
                                ->limit(10)
                                ->get()
            ]
        );
    }
}
