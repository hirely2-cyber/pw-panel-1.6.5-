<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MailController extends Controller
{
    public function index()
    {
        $this->authorize_('mail.send');

        $history = DB::connection('mysql')->table('mail_history')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        return view('panel.mail.index', compact('history'));
    }

    public function send(Request $request)
    {
        $this->authorize_('mail.send');

        $data = $request->validate([
            'recipient_name' => ['required', 'string', 'max:64'],
            'title'          => ['required', 'string', 'max:255'],
            'message'        => ['required', 'string', 'max:2000'],
            'item_id'        => ['nullable', 'integer', 'min:0'],
            'item_count'     => ['nullable', 'integer', 'min:0'],
            'money'          => ['nullable', 'integer', 'min:0'],
        ]);

        $role = DB::connection('pw_game')->table('roles')->where('name', $data['recipient_name'])->first();
        if (! $role) {
            return back()->withErrors(['recipient_name' => 'Karakter tidak ditemukan.'])->withInput();
        }

        DB::table('mail_history')->insert([
            'gm_user_id'       => auth('panel')->id(),
            'recipient_role_id' => $role->id,
            'recipient_name'   => $data['recipient_name'],
            'title'            => $data['title'],
            'message'          => $data['message'],
            'item_id'          => $data['item_id'] ?? null,
            'item_count'       => $data['item_count'] ?? 0,
            'money'            => $data['money'] ?? 0,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return back()->with('ok', 'Mail tercatat. Pengiriman real ke game memerlukan daemon socket aktif.');
    }

    protected function authorize_(string $perm): void
    {
        if (! auth('panel')->user()->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
