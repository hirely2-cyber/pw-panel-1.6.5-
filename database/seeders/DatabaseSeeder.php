<?php

namespace Database\Seeders;

use App\Models\Panel\GmRole;
use App\Models\Panel\GmUser;
use App\Models\Panel\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $defs = [
                'character' => [
                    'character.view'          => 'Lihat daftar karakter',
                    'character.edit.visual'   => 'Edit karakter (visual)',
                    'character.edit.xml'      => 'Edit karakter (XML)',
                    'character.rename'        => 'Rename karakter',
                    'character.level_up'      => 'Level up karakter',
                    'character.teleport'      => 'Teleport karakter',
                    'character.null_exp_sp'   => 'Reset EXP & SP',
                    'character.null_passwd'   => 'Reset password bank',
                    'character.delete'        => 'Hapus karakter',
                    'character.kick'          => 'Kick karakter dari server',
                    'character.ban'           => 'Ban karakter / chat / akun',
                ],
                'account' => [
                    'account.view'   => 'Lihat daftar akun',
                    'account.create' => 'Buat akun player',
                    'account.gm'     => 'Kelola GM-rights (tabel auth)',
                    'account.cash'   => 'Tambah gold / cash',
                ],
                'mail' => [
                    'mail.send'      => 'Kirim mail ke player',
                    'mail.send_all'  => 'Kirim mail massal ke online',
                ],
                'server' => [
                    'server.view'    => 'Lihat status server',
                    'server.control' => 'Start / Stop / Restart service',
                    'server.console' => 'Baca console (screen)',
                ],
                'chat' => [
                    'chat.read'      => 'Lihat chat log',
                    'chat.send'      => 'Kirim pesan GM',
                ],
                'panel' => [
                    'panel.settings' => 'Kelola pengaturan panel',
                    'panel.users'    => 'Kelola GM user & role',
                    'panel.logs'     => 'Lihat audit log',
                    'panel.servers'  => 'Kelola profil game server',
                ],
            ];

            foreach ($defs as $group => $items) {
                foreach ($items as $name => $label) {
                    Permission::firstOrCreate(
                        ['name' => $name],
                        ['group' => $group, 'label' => $label],
                    );
                }
            }

            $super = GmRole::firstOrCreate(
                ['name' => 'Super Admin'],
                ['color' => '#C9A24C', 'is_super' => true],
            );

            $gm = GmRole::firstOrCreate(
                ['name' => 'GM'],
                ['color' => '#2AA89A', 'is_super' => false],
            );

            $viewer = GmRole::firstOrCreate(
                ['name' => 'Viewer'],
                ['color' => '#5B8FB9', 'is_super' => false],
            );

            $gm->permissions()->sync(
                Permission::whereIn('group', ['character', 'account', 'mail', 'chat', 'server'])
                    ->whereNotIn('name', ['character.delete', 'account.gm', 'server.control'])
                    ->pluck('id')
            );

            $viewer->permissions()->sync(
                Permission::where('name', 'like', '%.view')
                    ->orWhere('name', 'chat.read')
                    ->pluck('id')
            );

            GmUser::firstOrCreate(
                ['username' => 'admin'],
                [
                    'email'        => 'admin@pw-panel.local',
                    'display_name' => 'Super Admin',
                    'password'     => Hash::make('admin123'),
                    'gm_role_id'   => $super->id,
                    'is_active'    => true,
                ],
            );
        });

        $this->command->info('Seed done. Default login: admin / admin123 (GANTI SETELAH LOGIN PERTAMA!)');
    }
}
