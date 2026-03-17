<?php

namespace App\Http\Controllers\Modules;

use App\Models\LoginLog;
use Illuminate\Http\Request;

class LoginLogController extends BaseModuleController
{
    public function __construct()
    {
        $this->requirePermission('login_logs', ['index'], [], [], [], []);
    }

    public function index(Request $request)
    {
        $query = LoginLog::with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        if ($request->filled('ip')) {
            $query->where('ip_address', 'like', '%' . $request->ip . '%');
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50)->withQueryString();

        $stats = [
            'total'   => LoginLog::count(),
            'success' => LoginLog::where('status', 'success')->count(),
            'failed'  => LoginLog::where('status', 'failed')->count(),
            'unique_ips' => LoginLog::distinct('ip_address')->count('ip_address'),
        ];

        $page_title = 'Giriş Logları';

        return view('modules.bilgi_islem.login_logs.index', compact('logs', 'stats', 'page_title'));
    }
}
