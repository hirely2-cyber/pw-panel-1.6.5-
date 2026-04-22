<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\PW\CashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize_('account.view');

        $q = $request->input('q');
        $query = DB::connection('pw_game')->table('users')
            ->select('ID', 'name', 'email', 'truename', 'gender', 'creatime');

        if ($q !== null && $q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('ID', $q);
            });
        }

        $accounts = $query->orderByDesc('creatime')->paginate(25)->withQueryString();

        return view('panel.account.index', compact('accounts', 'q'));
    }

    public function show(string $id)
    {
        $this->authorize_('account.view');

        $account = DB::connection('pw_game')->table('users')->where('ID', $id)->first();
        abort_unless($account, 404);

        $chars = DB::connection('pw_game')->table('roles')
            ->where('account_id', $id)
            ->select('role_id as id', 'role_name as name', 'role_race as race', 'role_occupation as cls', 'role_gender as gender', 'role_level as level')
            ->get();

        $gmRight = DB::connection('pw_game')->table('auth')->where('userid', $id)->first();

        return view('panel.account.show', compact('account', 'chars', 'gmRight'));
    }

    public function updatePassword(Request $request, string $id)
    {
        $this->authorize_('account.create');

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6', 'max:32'],
        ]);

        $account = DB::connection('pw_game')->table('users')->where('ID', $id)->first();
        abort_unless($account, 404);

        $mode = \App\Services\PW\Server::current()->password_hash;
        $hash = \App\Services\PW\PasswordHasher::hash($account->name, $data['password'], $mode);

        DB::connection('pw_game')->table('users')->where('ID', $id)->update(['passwd' => $hash]);

        return back()->with('ok', 'Password akun berhasil diganti.');
    }

    public function addCubi(Request $request, string $id)
    {
        $this->authorize_('account.create');

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:9999999'],
        ]);

        $account = DB::connection('pw_game')->table('users')->where('ID', $id)->first();
        abort_unless($account, 404);

        $ok = (new CashService)->addCubi((int) $account->ID, (int) $data['amount']);

        if ($ok) {
            return back()->with('ok', number_format($data['amount']) . ' Cubi berhasil ditambahkan ke akun ' . $account->name . '.');
        }

        return back()->with('error', 'Gagal menambahkan Cubi. Pastikan game server menyala.');
    }

    protected function authorize_(string $perm): void
    {
        if (! auth('panel')->user()->can($perm)) {
            abort(403, "Missing permission: {$perm}");
        }
    }
}
