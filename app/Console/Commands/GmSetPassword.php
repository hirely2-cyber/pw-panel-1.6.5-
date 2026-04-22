<?php

namespace App\Console\Commands;

use App\Models\Panel\GmUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GmSetPassword extends Command
{
    protected $signature = 'gm:set-password {username} {--password=}';
    protected $description = 'Set / reset password GM panel user';

    public function handle(): int
    {
        $user = GmUser::where('username', $this->argument('username'))->first();
        if (! $user) {
            $this->error("User '{$this->argument('username')}' tidak ditemukan.");
            return self::FAILURE;
        }

        $password = $this->option('password') ?: $this->secret('Password baru');
        if (! $password || strlen($password) < 8) {
            $this->error('Password minimal 8 karakter.');
            return self::FAILURE;
        }

        if (! $this->option('password')) {
            $confirm = $this->secret('Ulangi password baru');
            if ($confirm !== $password) {
                $this->error('Konfirmasi tidak cocok.');
                return self::FAILURE;
            }
        }

        $user->update(['password' => Hash::make($password)]);
        $this->info("Password untuk '{$user->username}' berhasil diganti.");
        return self::SUCCESS;
    }
}
