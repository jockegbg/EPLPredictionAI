<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    public function index(User $user)
    {
        // 1. Get Logins (Sessions) paginated
        $logins = ActivityLog::where('user_id', $user->id)
            ->where('action', 'login')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // 2. For each login, find the logs that belong to it
        $logins->getCollection()->transform(function ($login) use ($user) {
            // Find the NEXT login (chronologically after this one) to define the end of this session
            $nextLogin = ActivityLog::where('user_id', $user->id)
                ->where('action', 'login')
                ->where('created_at', '>', $login->created_at)
                ->orderBy('created_at', 'asc')
                ->first();

            $query = ActivityLog::where('user_id', $user->id)
                ->where('created_at', '>=', $login->created_at);

            if ($nextLogin) {
                $query->where('created_at', '<', $nextLogin->created_at);
            }

            // Get all logs for this session, excluding the login itself if you want (or keep it)
            // Let's keep it but maybe we don't need to show it in the expanded list if it's the header.
            // Actually, let's just get everything ordered DESC
            $sessionLogs = $query->orderBy('created_at', 'desc')->get();

            $login->session_logs = $sessionLogs;
            return $login;
        });

        return response()->json($logins);
    }
}
