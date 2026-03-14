<?php

namespace App\Http\Controllers\Modules;

use App\Models\UserTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyTaskController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('my_tasks',
            ['index'],
            [],
            ['store'],
            ['update'],
            ['destroy']
        );
    }

    public function index()
    {
        $tasks = UserTask::where('user_id', Auth::id())
            ->orderByRaw("FIELD(status, 'in_progress', 'pending', 'completed')")
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('created_at', 'desc')
            ->get();

        return view('modules.islerim.index', compact('tasks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date|after_or_equal:today',
            'priority'    => 'required|in:low,medium,high',
        ]);

        $data['user_id'] = Auth::id();
        $data['status']  = 'pending';

        UserTask::create($data);

        return back()->with('success', 'Görev eklendi.');
    }

    public function update(Request $request, UserTask $userTask)
    {
        $this->authorizeTask($userTask);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date'    => 'nullable|date',
            'priority'    => 'required|in:low,medium,high',
            'status'      => 'required|in:pending,in_progress,completed',
        ]);

        // Eğer bitirildi olarak işaretleniyorsa reminder_sent'i sıfırla (gerekirse)
        $userTask->update($data);

        return back()->with('success', 'Görev güncellendi.');
    }

    public function complete(UserTask $userTask)
    {
        $this->authorizeTask($userTask);
        $userTask->update(['status' => 'completed']);
        return back()->with('success', 'Görev tamamlandı olarak işaretlendi.');
    }

    public function destroy(UserTask $userTask)
    {
        $this->authorizeTask($userTask);
        $userTask->delete();
        return back()->with('success', 'Görev silindi.');
    }

    private function authorizeTask(UserTask $task): void
    {
        if ($task->user_id !== Auth::id()) {
            abort(403, 'Bu göreve erişim yetkiniz yok.');
        }
    }
}
