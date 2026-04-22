<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Permission master list
        Schema::create('permissions', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100)->unique();   // eg: "character.edit.visual"
            $t->string('group', 50);              // "character", "server", "mail"
            $t->string('label');                  // human label
            $t->timestamps();
        });

        // GM permission groups / roles (hak akses panel)
        Schema::create('gm_roles', function (Blueprint $t) {
            $t->id();
            $t->string('name', 100)->unique();
            $t->string('color', 16)->default('#C9A24C');
            $t->boolean('is_super')->default(false);
            $t->timestamps();
        });

        Schema::create('gm_role_permissions', function (Blueprint $t) {
            $t->foreignId('gm_role_id')->constrained('gm_roles')->cascadeOnDelete();
            $t->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $t->primary(['gm_role_id', 'permission_id']);
        });

        // GM users (akun admin panel)
        Schema::create('gm_users', function (Blueprint $t) {
            $t->id();
            $t->string('username', 64)->unique();
            $t->string('email', 190)->unique();
            $t->string('display_name', 100)->nullable();
            $t->string('password');                        // argon2id
            $t->foreignId('gm_role_id')->nullable()->constrained('gm_roles')->nullOnDelete();
            $t->string('two_factor_secret')->nullable();
            $t->text('two_factor_recovery_codes')->nullable();
            $t->timestamp('two_factor_confirmed_at')->nullable();
            $t->string('last_login_ip', 45)->nullable();
            $t->timestamp('last_login_at')->nullable();
            $t->boolean('is_active')->default(true);
            $t->rememberToken();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gm_users');
        Schema::dropIfExists('gm_role_permissions');
        Schema::dropIfExists('gm_roles');
        Schema::dropIfExists('permissions');
    }
};
