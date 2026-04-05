<?php
// app/Http/Controllers/Admin/LogController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * Display a listing of the logs.
     */
    public function index(Request $request)
    {
        $query = Log::with('user')->latest();

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);
        
        $modules = Log::distinct('module')->pluck('module');
        $actionTypes = Log::distinct('action_type')->pluck('action_type');

        return view('admin.logs.index', compact('logs', 'modules', 'actionTypes'));
    }

    /**
     * Display the specified log.
     */
    public function show(Log $log)
    {
        $log->load('user');
        return view('admin.logs.show', compact('log'));
    }

    /**
     * Clear old logs.
     */
    public function clear(Request $request)
    {
        $days = $request->get('days', 90);
        
        $deleted = Log::where('created_at', '<', now()->subDays($days))->delete();

        return redirect()->route('admin.logs.index')
            ->with('success', "{$deleted} old logs cleared successfully.");
    }
}