<?php

/*
|--------------------------------------------------------------------------
| Perfect World Panel Configuration
|--------------------------------------------------------------------------
|
| Config ini HANYA dipakai sebagai bootstrap / fallback awal.
| Nilai sesungguhnya yang digunakan runtime diambil dari tabel
| "game_servers" di database panel (bisa diedit dari halaman Settings).
|
*/

return [

    // Koneksi database untuk game (pw172, pw, dll.) — diset di .env
    'game_db' => env('PW_DB_CONNECTION', 'pw_game'),

    // Bootstrap default — disalin ke DB saat `php artisan pw:bootstrap` pertama kali
    'bootstrap' => [
        'name'             => env('PW_BOOT_NAME', 'Default Server'),
        'socket_host'      => env('PW_BOOT_SOCKET_HOST', '127.0.0.1'),
        'socket_port'      => (int) env('PW_BOOT_SOCKET_PORT', 65000),
        'server_key'       => env('PW_BOOT_SERVER_KEY', ''),
        'version'          => env('PW_BOOT_VERSION', '1.7.6'),
        'db_port'          => (int) env('PW_BOOT_DB_PORT', 29400),
        'gdeliveryd_port'  => (int) env('PW_BOOT_GDELIVERYD_PORT', 29100),
        'gprovider_port'   => (int) env('PW_BOOT_GPROVIDER_PORT', 29300),
        'link_port'        => (int) env('PW_BOOT_LINK_PORT', 29000),
        'server_path'      => env('PW_BOOT_SERVER_PATH', '/home'),
        'logs_path'        => env('PW_BOOT_LOGS_PATH', '/home/logs'),
        'auth_type'        => env('PW_BOOT_AUTH_TYPE', 'gauthd'),
        'chat_file'        => env('PW_BOOT_CHAT_FILE', 'world2.chat'),
        'password_hash'    => env('PW_BOOT_PASSWORD_HASH', 'base64'),
    ],

    // Versi PW yang didukung — tiap versi punya struct character/opcode berbeda
    'supported_versions' => ['1.7.2', '1.7.4', '1.7.6', '1.8.0'],

    // Opsi algoritma hash password untuk gauthd
    'password_hashes' => [
        'base64' => 'base64(md5(user+pass)) — PW default',
        'md5'    => 'md5(user+pass)',
        '0x.md5' => '"0x" . md5(user+pass)',
        'plain'  => 'Plain text (TIDAK DIREKOMENDASIKAN)',
    ],

    // Daftar service PW server yang bisa dikelola panel
    'services' => [
        'logservice'  => ['dir' => 'logservice',  'program' => 'logservice',  'config' => 'logservice.conf'],
        'uniquenamed' => ['dir' => 'uniquenamed', 'program' => 'uniquenamed', 'config' => 'gamesys.conf'],
        'auth'        => ['dir' => 'gauthd',      'program' => 'gauthd',      'config' => 'gamesys.conf'],
        'gamedbd'     => ['dir' => 'gamedbd',     'program' => 'gamedbd',     'config' => 'gamesys.conf'],
        'gacd'        => ['dir' => 'gacd',        'program' => 'gacd',        'config' => 'gamesys.conf'],
        'gfactiond'   => ['dir' => 'gfactiond',   'program' => 'gfactiond',   'config' => 'gamesys.conf'],
        'gdeliveryd'  => ['dir' => 'gdeliveryd',  'program' => 'gdeliveryd',  'config' => 'gamesys.conf'],
        'glinkd'      => ['dir' => 'glinkd',      'program' => 'glinkd',      'config' => 'gamesys.conf', 'instances' => 4],
        'gs'          => ['dir' => 'gamed',       'program' => 'gs',          'config' => 'gs01'],
    ],

    // Urutan shutdown aman (graceful stop) — kebalikan dari start_order,
    // stop dari bawah (gs) naik ke atas (logservice).
    'stop_order' => [
        'gs', 'glinkd', 'gdeliveryd', 'gfactiond', 'gacd',
        'gamedbd', 'auth', 'uniquenamed', 'logservice',
    ],

    // Direktori tempat file backup tar.gz / sql disimpan
    'backup_dir' => env('PW_BACKUP_DIR', storage_path('app/pw-backups')),

    // Socket timeout & retry
    'socket' => [
        'connect_timeout' => 5,
        'read_timeout'    => 10,
        'max_retries'     => 2,
    ],

    // Urutan start aman (tiap service menunggu dependency-nya ready).
    // Format: key => delay_after_start (detik) sebelum service berikutnya.
    'start_order' => [
        'logservice'  => 1,
        'uniquenamed' => 1,
        'auth'        => 2,   // gauthd perlu sebentar untuk listen
        'gamedbd'     => 4,   // database daemon, tunggu lebih lama
        'gacd'        => 2,
        'gfactiond'   => 2,
        'gdeliveryd'  => 3,   // konek ke gamedbd
        'glinkd'      => 2,
        'gs'          => 1,
    ],

    // Delay (detik) antar service saat stopAll.
    'stop_delay' => 1,
];
