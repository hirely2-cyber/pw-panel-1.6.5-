<?php

use App\Http\Controllers\Panel\AuthController;
use App\Http\Controllers\Panel\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Panel (GM / Admin) Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => redirect()->route('panel.dashboard'));

// Auth
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware(['panel.auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard',         [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/server-status', [DashboardController::class, 'status'])->name('server.status');

    // Accounts
    Route::get('/accounts',                 [\App\Http\Controllers\Panel\AccountController::class, 'index'])->name('accounts.index');
    Route::get('/accounts/{id}',            [\App\Http\Controllers\Panel\AccountController::class, 'show'])->name('accounts.show');
    Route::post('/accounts/{id}/password',  [\App\Http\Controllers\Panel\AccountController::class, 'updatePassword'])->name('accounts.password');
    Route::post('/accounts/{id}/cubi',      [\App\Http\Controllers\Panel\AccountController::class, 'addCubi'])->name('accounts.cubi');

    // Characters
    Route::get('/characters',            [\App\Http\Controllers\Panel\CharacterController::class, 'index'])->name('characters.index');
    Route::get('/characters/{id}',       [\App\Http\Controllers\Panel\CharacterController::class, 'show'])->name('characters.show');
    Route::post('/characters/{id}',      [\App\Http\Controllers\Panel\CharacterController::class, 'update'])->name('characters.update');
    Route::get('/characters/{id}/xml',   [\App\Http\Controllers\Panel\CharacterController::class, 'rawXml'])->name('characters.xml');
    Route::post('/characters/{id}/xml',  [\App\Http\Controllers\Panel\CharacterController::class, 'saveXml'])->name('characters.xml.save');

    // Server
    Route::get('/server',              [\App\Http\Controllers\Panel\ServerController::class, 'index'])->name('server.index');
    Route::post('/server/control',     [\App\Http\Controllers\Panel\ServerController::class, 'control'])->name('server.control');
    Route::post('/server/control/stream', [\App\Http\Controllers\Panel\ServerController::class, 'controlStream'])
        ->name('server.control.stream');
    Route::post('/server/broadcast',   [\App\Http\Controllers\Panel\ServerController::class, 'broadcast'])
        ->name('server.broadcast');
    Route::get('/server/backup/{name}',[\App\Http\Controllers\Panel\ServerController::class, 'downloadBackup'])
        ->where('name', '[A-Za-z0-9._-]+')
        ->name('server.backup.download');    Route::delete('/server/backup/{name}', [\App\Http\Controllers\Panel\ServerController::class, 'deleteBackup'])
        ->where('name', '[A-Za-z0-9._-]+')
        ->name('server.backup.delete');
    // Mail
    Route::get('/mail',      [\App\Http\Controllers\Panel\MailController::class, 'index'])->name('mail.index');
    Route::post('/mail',     [\App\Http\Controllers\Panel\MailController::class, 'send'])->name('mail.send');

    // Chat
    Route::get('/chat',      [\App\Http\Controllers\Panel\ChatController::class, 'index'])->name('chat.index');

    // Settings (Game Server profiles)
    Route::get('/settings',                     [\App\Http\Controllers\Panel\SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/{server}',            [\App\Http\Controllers\Panel\SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/{server}',            [\App\Http\Controllers\Panel\SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/{server}/default',   [\App\Http\Controllers\Panel\SettingsController::class, 'setDefault'])->name('settings.default');

    // GM users & roles
    Route::get('/gm-users',                [\App\Http\Controllers\Panel\GmUserController::class, 'index'])->name('gm.index');
    Route::post('/gm-users',               [\App\Http\Controllers\Panel\GmUserController::class, 'store'])->name('gm.store');
    Route::put('/gm-users/{user}',         [\App\Http\Controllers\Panel\GmUserController::class, 'update'])->name('gm.update');
    Route::delete('/gm-users/{user}',      [\App\Http\Controllers\Panel\GmUserController::class, 'destroy'])->name('gm.destroy');
    Route::get('/gm-roles',                [\App\Http\Controllers\Panel\GmUserController::class, 'roles'])->name('gm.roles');
    Route::put('/gm-roles/{role}',         [\App\Http\Controllers\Panel\GmUserController::class, 'updateRole'])->name('gm.roles.update');

    // Audit logs
    Route::get('/logs', [\App\Http\Controllers\Panel\AuditLogController::class, 'index'])->name('logs.index');

    // Profile
    Route::get('/profile',          [\App\Http\Controllers\Panel\ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile',          [\App\Http\Controllers\Panel\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [\App\Http\Controllers\Panel\ProfileController::class, 'changePassword'])->name('profile.password');
});
