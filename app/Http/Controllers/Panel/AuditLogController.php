<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Panel\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (! auth('panel')->user()->can('panel.logs')) {
            abort(403);
        }
        $logs = AuditLog::with('user')->orderByDesc('id')->paginate(50);
        return view('panel.logs.index', compact('logs'));
    }
}
