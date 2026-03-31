<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Task;

class TasksController extends Controller
{

    public function index(Request $request)
    {
        $query = Task::query();

        // filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // We assign 3 to high, 2 to medium, 1 to low to ensure high comes first
        $tasks = $query
            ->orderByRaw("
                CASE 
                    WHEN priority = 'high' THEN 1
                    WHEN priority = 'medium' THEN 2
                    WHEN priority = 'low' THEN 3
                END
            ")
            ->orderBy('due_date', 'asc')
            ->get();

        // If no tasks
        if ($tasks->isEmpty()) {
            return response()->json([
                'message' => 'No tasks found'
            ]);
        }

        return response()->json($tasks);
    }


    
    public function store(Request $request){
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:pending,in_progress,done',
        ]);

        $exists = Task::where('title', $validated['title'])
                  ->where('due_date', $validated['due_date'])
                  ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'Task with same title and due date already exists'
            ], 422);
        }

        // Create task
        $task = Task::create([
            'title' => $validated['title'],
            'due_date' => $validated['due_date'],
            'priority' => $validated['priority'],
            'status' => 'pending' // default
        ]);

        // Return created task
        return response()->json($task, 201);
        
    }

    public function updateStatus(Request $request, $id)
    {
        $task = Task::find($id);

        // If task not found
        if (!$task) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        // Validate incoming status
        $request->validate([
            'status' => 'required|in:pending,in_progress,done'
        ]);

        $newStatus = $request->status;
        $currentStatus = $task->status;

        // Define allowed transitions
        $allowedTransitions = [
            'pending' => 'in_progress',
            'in_progress' => 'done',
        ];

        // If task is already done
        if ($currentStatus === 'done') {
            return response()->json([
                'error' => 'Cannot change status of a completed task'
            ], 400);
        }

        // If trying to skip or revert
        if (!isset($allowedTransitions[$currentStatus]) || $allowedTransitions[$currentStatus] !== $newStatus) {
            return response()->json([
                'error' => "Invalid status transition from $currentStatus to $newStatus"
            ], 422);
        }

        // Update status
        $task->status = $newStatus;
        $task->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'task' => $task
        ]);
    }

    public function destroy($id)
    {
        $task = Task::find($id);

        // If task not found
        if (!$task) {
            return response()->json([
                'error' => 'Task not found'
            ], 404);
        }

        // If task is NOT done
        if ($task->status !== 'done') {
            return response()->json([
                'error' => 'Only completed tasks can be deleted'
            ], 403); // 
        }

        // Delete task
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }


    public function report(Request $request)
    {
        // Validate date
        $request->validate([
            'date' => 'required|date'
        ]);

        $date = $request->date;

        // Initialize structure
        $summary = [
            'high' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
            'medium' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
            'low' => ['pending' => 0, 'in_progress' => 0, 'done' => 0],
        ];

        // Get tasks for that date
        $tasks = Task::whereDate('due_date', $date)->get();

        // Count
        foreach ($tasks as $task) {
            $priority = $task->priority;
            $status = $task->status;

            if (isset($summary[$priority][$status])) {
                $summary[$priority][$status]++;
            }
        }

        // Return response
        return response()->json([
            'date' => $date,
            'summary' => $summary
        ]);
    }

}
