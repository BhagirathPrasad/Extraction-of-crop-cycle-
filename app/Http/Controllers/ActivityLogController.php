<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{

    public function index(Request $request): View
    {
        $query = ActivityLog::with('user');

        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('action'))  $query->where('action', $request->action);
        if ($request->filled('date'))    $query->whereDate('created_at', $request->date);

        $logs = $query->latest()->paginate(30)->withQueryString();

        return view('activity-logs.index', compact('logs'));
    }
}
