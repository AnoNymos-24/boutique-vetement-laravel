<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->user_id, fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($request->action, fn ($q, $action) => $q->where('action', 'like', "{$action}%"))
            ->when($request->date_from, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->date_to, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('activity-log.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(),
            'actionGroups' => ActivityLog::query()
                ->selectRaw("SUBSTRING_INDEX(action, '.', 1) as group_name")
                ->distinct()
                ->orderBy('group_name')
                ->pluck('group_name'),
        ]);
    }
}
