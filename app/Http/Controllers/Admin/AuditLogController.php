<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->latest();

        if ($request->filled('user_id')) {
            $query->where('causer_id', $request->user_id)
                  ->where('causer_type', \App\Models\User::class);
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(30)->withQueryString()
            ->through(fn ($log) => [
                'id'          => $log->id,
                'log_name'    => $log->log_name,
                'description' => $log->description,
                'causer_name' => $log->causer?->name ?? 'Sistem',
                'causer_role' => $log->causer?->role ?? '-',
                'properties'  => $log->properties?->toArray() ?? [],
                'created_at'  => $log->created_at->format('d M Y H:i:s'),
            ]);

        $users = \App\Models\User::orderBy('name')->get(['id', 'name', 'role']);

        $logNames = Activity::distinct()->pluck('log_name')->filter()->values();

        return Inertia::render('Admin/AuditLog', [
            'logs'     => $logs,
            'users'    => $users,
            'logNames' => $logNames,
            'filters'  => $request->only(['user_id', 'log_name', 'date_from', 'date_to']),
        ]);
    }
}
