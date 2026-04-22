<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Config server PW — dinamis, bisa multiple profil (dev / prod / server2)
        Schema::create('game_servers', function (Blueprint $t) {
            $t->id();
            $t->string('name', 80);
            $t->string('slug', 80)->unique();
            $t->string('version', 16)->default('1.7.6');
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);

            // Koneksi daemon perantara
            $t->string('socket_host', 80)->default('127.0.0.1');
            $t->unsignedSmallInteger('socket_port')->default(65000);
            $t->string('server_key', 128);

            // Port daemon PW
            $t->unsignedSmallInteger('db_port')->default(29400);
            $t->unsignedSmallInteger('gdeliveryd_port')->default(29100);
            $t->unsignedSmallInteger('gprovider_port')->default(29300);
            $t->unsignedSmallInteger('link_port')->default(29000);

            // Path filesystem (dibaca via daemon)
            $t->string('server_path', 255)->default('/home');
            $t->string('logs_path', 255)->default('/home/logs');

            // Misc
            $t->string('auth_type', 32)->default('gauthd');
            $t->string('chat_file', 64)->default('world2.chat');
            $t->string('password_hash', 32)->default('base64');

            // Koneksi DB game
            $t->string('db_host', 80)->default('127.0.0.1');
            $t->unsignedSmallInteger('db_port_mysql')->default(3306);
            $t->string('db_name', 64)->default('pw172');
            $t->string('db_user', 64)->default('root');
            $t->string('db_password', 255)->nullable();

            $t->timestamps();
        });

        // Audit log semua aksi di panel
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gm_user_id')->nullable()->constrained('gm_users')->nullOnDelete();
            $t->string('action', 100);                  // "character.edit", "server.restart"
            $t->string('target_type', 64)->nullable();  // "character", "account", "service"
            $t->string('target_id', 64)->nullable();
            $t->string('ip', 45)->nullable();
            $t->string('user_agent', 255)->nullable();
            $t->json('payload')->nullable();            // data yang dikirim
            $t->json('result')->nullable();             // hasil / response
            $t->boolean('success')->default(true);
            $t->timestamp('created_at')->useCurrent();
            $t->index(['gm_user_id', 'created_at']);
            $t->index(['action', 'created_at']);
        });

        // Riwayat mail yang dikirim dari panel
        Schema::create('mail_history', function (Blueprint $t) {
            $t->id();
            $t->foreignId('gm_user_id')->nullable()->constrained('gm_users')->nullOnDelete();
            $t->unsignedBigInteger('recipient_role_id');
            $t->string('recipient_name', 64)->nullable();
            $t->string('title', 255);
            $t->text('message')->nullable();
            $t->unsignedInteger('item_id')->nullable();
            $t->unsignedInteger('item_count')->default(0);
            $t->unsignedInteger('money')->default(0);
            $t->json('payload')->nullable();
            $t->timestamps();
            $t->index('recipient_role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_history');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('game_servers');
    }
};
