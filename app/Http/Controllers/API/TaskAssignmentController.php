<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TaskAssignmentController extends Controller
{
    protected const DAILY_TASKS_NUMBER = 3;
    protected static $typesMap = [
        1 => 'Fundamentals',
        2 => 'String',
        3 => 'Algorithms',
        4 => 'Mathematics',
        5 => 'Performance',
        6 => 'Booleans',
        7 => 'Functions',
    ];
    /**
     * @var int|null
     */
    protected $userId;

    public function __construct()
    {
        $user = \request()->user('sanctum');
        if ($user instanceof User) {
            $this->userId = $user->getAttribute('id');
        }
    }

	/**
	 * @param int $limit
	 * @return Collection
	 */
    protected function assignNewTasks(int $limit = self::DAILY_TASKS_NUMBER): Collection
    {
	    $assignedTaskIds = DB::table('task_assignments')
		    ->select(['task_id'])
		    ->where('user_id', '=', $this->userId)
		    ->get()->pluck('task_id')->toArray();
	    $result = DB::table('tasks')
		    ->select('id', 'type', 'text')
		    ->whereNotIn('tasks.id', $assignedTaskIds)
		    ->orderByRaw('random()')
		    ->limit($limit)
		    ->get();
		$newAssignData = [];
		$createdAt = (string)Carbon::today();
		foreach ($result as $task) {
			$newAssignData[] = [
				'user_id' => $this->userId,
				'task_id' => $task->id,
				'solved' => false,
				'skipped' => false,
				'created_at' => $createdAt,
			];
		}
		DB::table('task_assignments')->insert($newAssignData);
		return $result;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        $tasks = $this->getTasksForToday();
        if ($tasks->isEmpty()) {
            $tasks = $this->assignNewTasks();
        }
        return response()->json([
            'status' => true,
            'tasks' => $this->tasksOutput($tasks),
        ], 200);
    }

    /**
     * @return Collection
     */
    protected function getTasksForToday(int $taskId = null): Collection
    {
        $query = DB::table('task_assignments')
            ->select(['tasks.id', 'tasks.type', 'tasks.text', 'task_assignments.solved', 'task_assignments.id as assignmentId'])
	        ->whereDate('task_assignments.created_at', Carbon::today())
            ->where('task_assignments.user_id', '=', $this->userId)
            ->where('task_assignments.skipped', '=', false)
            ->leftJoin('tasks', 'task_assignments.task_id', '=', 'tasks.id');
		if (isset($taskId)) {
			$query->where('task_assignments.task_id', '=', $taskId);
		}
		return $query->get();
    }

    /**
     * @param Request $request
     * @param integer $taskId
     * @return JsonResponse
     */
    public function skip(Request $request, int $taskId): JsonResponse
    {
	    $assignmentId = $this->getTasksForToday($taskId)->pluck('assignmentId')->first();
	    if (isset($assignmentId)) {
		    DB::table('task_assignments')->where('id', '=', $assignmentId)->update(['skipped' => true]);
		    return response()->json([
			    'status' => true,
			    'message' => 'Task replaced successfully',
			    'new_task' => $this->assignNewTasks(1),
		    ], 200);
	    }

	    return response()->json([
		    'status' => false,
		    'message' => 'Task not found',
	    ], 500);
    }

    /**
     * @param Request $request
     * @param integer $taskId
     * @return JsonResponse
     */
    public function solve(Request $request, int $taskId): JsonResponse
    {
	    $task = $this->getTasksForToday($taskId)->first();
	    if (isset($task)) {
			if ($task->solved) {
				return response()->json([
					'status' => true,
					'message' => 'Task already solved'
				], 500);
			}
		    DB::table('task_assignments')->where('id', '=', $task->assignmentId)->update(['solved' => true]);
		    return response()->json([
			    'status' => true,
			    'message' => 'Task solved successfully'
		    ], 200);
	    }

	    return response()->json([
		    'status' => false,
		    'message' => 'Task not found',
	    ], 500);
    }

    /**
     * @param Collection $tasks
     * @return array
     */
    protected function tasksOutput(Collection $tasks): array
    {
		$map = self::$typesMap;
		return $tasks->each(function ($item) use ($map) {
			$item->type = $map[$item->type];
        })->toArray();
    }
}
