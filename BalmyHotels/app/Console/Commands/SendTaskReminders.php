<?php

namespace App\Console\Commands;

use App\Mail\TaskDeadlineReminder;
use App\Models\UserTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTaskReminders extends Command
{
    protected $signature   = 'tasks:send-reminders';
    protected $description = 'Yarın son tarihi olan görevler için e-posta hatırlatıcısı gönder';

    public function handle(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        $tasks = UserTask::with('user')
            ->where('due_date', $tomorrow)
            ->where('status', '!=', 'completed')
            ->where('reminder_sent', false)
            ->get();

        $sent = 0;
        foreach ($tasks as $task) {
            if (!$task->user || !$task->user->email) {
                continue;
            }

            try {
                Mail::to($task->user->email)->send(new TaskDeadlineReminder($task));
                $task->update(['reminder_sent' => true]);
                $sent++;
            } catch (\Throwable $e) {
                $this->error("Görev #{$task->id} için e-posta gönderilemedi: " . $e->getMessage());
            }
        }

        $this->info("{$sent} hatırlatıcı e-posta gönderildi.");
    }
}
