<?php

namespace App\Console\Commands;

use App\Models\Panel\GmRole;
use App\Models\Panel\GmUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GmCreate extends Command
{
    protected $signature = 'gm:create {username} {--email=} {--role=Super Admin} {--password=}';
    protected $description = 'Create new GM panel user';

    public function handle(): int
    {
        if (GmUser::where('username', $this->argument('username'))->exists()) {
            $this->error('Username sudah ada.');
            return self::FAILURE;
        }

        $role = GmRole::where('name', $this->option('role'))->first();
        if (! $role) {
            $this->error("Role '{$this->option('role')}' tidak ada.");
            return self::FAILURE;
        }

        $password = $this->option('password') ?: $this->secret('Password');
        $email    = $this->option('email') ?: $this->ask('Email');

        GmUser::create([
            'username'   => $this->argument('username'),
            'email'      => $email,
            'password'   => Hash::make($password),
            'gm_role_id' => $role->id,
            'is_active'  => true,
        ]);

        $this->info('GM user dibuat.');
        return self::SUCCESS;
    }
}
