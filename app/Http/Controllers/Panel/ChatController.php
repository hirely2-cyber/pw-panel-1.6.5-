<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index()
    {
        $this->authorize_('chat.read');

        $logs = [];
        try {
            $logs = DB::connection('pw_game')->table('log_chat')
                ->orderByDesc('time')->limit(200)->get();
        } catch (\Throwable $e) {
            // Table tidak ada di semua version
        }

        return view('panel.chat.index', compact('logs'));
    }

    protected function authorize_(string $perm): void
    {
        if (! auth('panel')->user()->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
