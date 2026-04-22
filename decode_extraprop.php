<?php
/**
 * Decode extraprop — compare 2 karakter
 *
 * Char1 (Wukong1 ID 1025):
 *   Detect Lvl = 105, Spirit = 0, Stealth = 0, Soulforce = 16275
 *
 * Char2 (teman, nilai 552):
 *   Soulforce = 552 (asumsi)
 */

$chars = [
    'Char1 (Wukong1, Detect=105, Soul=16275)' => [
        'hex'    => '07000000011469e884740000000000000000000000000201000000000003040000000a000000053c0004ec6900000000060000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000644080000001080e869108ee8695a65e8690061e969600be66900a7ee69600be66900edf3691080e869108ee8695a65e8690061e969600be66900a7ee69600be66900edf369000000080800000000000000000000000d0800000000000000000000000e0400000000',
        'known'  => [105 => 'Detect Lvl', 16275 => 'Soulforce', 0 => 'Spirit/Stealth'],
    ],
    'Char2 (teman, Soul=552)' => [
        'hex'    => '080000000114695406be0000000000000000000000000001000000000003040000000a000000040e000000000000000000000000f003000000053c60683d7d0000000006000000080000000000000008000000000000000800000000000000080000000000000008000000000000000800000000000000000000064408000000b47be8690080e869b47be86990a3e869b47be86910e0ee697e2ce169904b026ab47be869a081e869b47be8691089e869b47be8691017f0697e2ce1691077076a000000080800000000000000000000000d0800000000000000000000000e0400000000',
        'known'  => [552 => 'Soulforce?'],
    ],
];

function ri32(string $bin, int &$p): int {
    if ($p + 4 > strlen($bin)) return 0;
    $r = unpack('l', substr($bin, $p, 4)); $p += 4; return $r[1];
}
function rbyte(string $bin, int &$p): int {
    if ($p + 1 > strlen($bin)) return 0;
    $r = unpack('C', substr($bin, $p, 1)); $p += 1; return $r[1];
}

// =====================================================
// 1. Dump aligned int32 per karakter
// =====================================================
$allDumps = [];
foreach ($chars as $label => $info) {
    $bin = hex2bin($info['hex']);
    $len = strlen($bin);
    echo "\n=== $label ===\n";
    echo "Bytes: $len\n";
    echo str_pad('Field#', 8) . str_pad('Offset', 10) . str_pad('Hex', 12) . "Value\n";
    echo str_repeat('-', 50) . "\n";
    $p = 0; $i = 0;
    $dump = [];
    while ($p + 4 <= $len) {
        $off = $p;
        $v = ri32($bin, $p);
        $h = bin2hex(substr($bin, $off, 4));
        printf("  [%02d]  0x%02X      %-12s %d\n", $i, $off, $h, $v);
        $dump[$i] = ['off' => $off, 'hex' => $h, 'val' => $v];
        $i++;
    }
    $allDumps[$label] = $dump;
}

// =====================================================
// 2. Brute-force cari known values (semua offset)
// =====================================================
echo "\n\n=== BRUTE-FORCE SEARCH (semua offset, bukan hanya aligned) ===\n";
foreach ($chars as $label => $info) {
    $bin = hex2bin($info['hex']);
    $len = strlen($bin);
    echo "\n-- $label --\n";
    foreach ($info['known'] as $needle => $name) {
        if ($needle === 0) continue; // skip 0, terlalu banyak
        $found = [];
        for ($off = 0; $off + 4 <= $len; $off++) {
            $v = unpack('l', substr($bin, $off, 4))[1];
            if ($v === $needle) $found[] = sprintf("0x%02X(field~%d)", $off, intdiv($off, 4));
        }
        if ($found) {
            printf("  %-30s = %-6d  at: %s\n", $name, $needle, implode(', ', $found));
        } else {
            printf("  %-30s = %-6d  NOT FOUND as int32\n", $name, $needle);
        }
    }
}

// =====================================================
// 3. Side-by-side compare field per field
// =====================================================
echo "\n\n=== SIDE-BY-SIDE (aligned int32 per field) ===\n";
$labels = array_keys($allDumps);
$dumps  = array_values($allDumps);
$maxF   = max(array_map('count', $dumps));

printf("  %-6s  %-14s  %-14s  %s\n", 'Field', $labels[0] ?? 'C1', $labels[1] ?? 'C2', 'DIFF?');
echo str_repeat('-', 80) . "\n";
for ($i = 0; $i < $maxF; $i++) {
    $v0 = $dumps[0][$i]['val'] ?? '?';
    $v1 = $dumps[1][$i]['val'] ?? '?';
    $off = sprintf("0x%02X", $dumps[0][$i]['off'] ?? 0);
    $diff = ($v0 !== $v1) ? '<-- BERBEDA' : '';
    printf("  [%02d] %-6s  %-14s  %-14s  %s\n", $i, $off, $v0, $v1, $diff);
}

// =====================================================
// 4. Decode dengan format octets diawali CUint size
//    (extraprop dibungkus octets: readCUint32 = size, lalu isinya)
//    Tapi karena kita sudah strip octets wrapper di GRoleReader,
//    isi langsung dimulai dari byte 0.
//    Coba decode: byte[0..3]=version int32, lalu baca satu-satu byte
//    sebagai "tipe data yang mungkin berbeda"
// =====================================================
echo "\n\n=== DECODE BYTE-BY-BYTE dump hex char1 (untuk lihat pattern) ===\n";
$bin = hex2bin($chars['Char1 (Wukong1, Detect=105, Soul=16275)']['hex']);
$len = strlen($bin);
// Print 16 bytes per row
for ($i = 0; $i < $len; $i += 16) {
    $chunk = substr($bin, $i, 16);
    $hexPart = implode(' ', str_split(bin2hex($chunk), 2));
    printf("  0x%02X: %s\n", $i, $hexPart);
}

echo "\nDone.\n";


$hex = '07000000011469e884740000000000000000000000000201000000000003040000000a000000053c0004ec6900000000060000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000644080000001080e869108ee8695a65e8690061e969600be66900a7ee69600be66900edf3691080e869108ee8695a65e8690061e969600be66900a7ee69600be66900edf369000000080800000000000000000000000d0800000000000000000000000e0400000000';

$bin = hex2bin($hex);
$len = strlen($bin);

echo "=== Extraprop decoder ===\n";
echo "Total bytes: $len\n\n";

// Helper: read LE int32 signed
function ri32(string $bin, int &$p): int {
    if ($p + 4 > strlen($bin)) return 0;
    $r = unpack('l', substr($bin, $p, 4));
    $p += 4;
    return $r[1];
}

// Helper: read LE int32 unsigned
function ri32u(string $bin, int &$p): int {
    if ($p + 4 > strlen($bin)) return 0;
    $r = unpack('V', substr($bin, $p, 4));
    $p += 4;
    return $r[1];
}

// Helper: read byte
function rbyte(string $bin, int &$p): int {
    if ($p + 1 > strlen($bin)) return 0;
    $r = unpack('C', substr($bin, $p, 1));
    $p += 1;
    return $r[1];
}

// Helper: read LE float
function rfloat(string $bin, int &$p): float {
    if ($p + 4 > strlen($bin)) return 0.0;
    $r = unpack('f', substr($bin, $p, 4));
    $p += 4;
    return round($r[1], 4);
}

// =====================================================
// 1. Dump semua sebagai sequence int32 LE
// =====================================================
echo "--- Dump sebagai int32 LE (setiap 4 byte) ---\n";
$p = 0;
$i = 0;
while ($p + 4 <= $len) {
    $off  = $p;
    $val  = ri32($bin, $p);
    $hex4 = bin2hex(substr($bin, $off, 4));
    $pu   = $off;
    $valu = ri32u($bin, $pu);
    printf("  [%03d] offset=0x%02X  hex=%-10s  signed=%-12d  unsigned=%d\n",
        $i++, $off, $hex4, $val, $valu);
}

// =====================================================
// 2. Cari nilai known: 105 (Detect Lvl), 16275 (Soulforce)
// =====================================================
echo "\n--- Cari nilai known dalam hex ---\n";
$knowns = [
    105    => 'Detect Lvl',
    0      => 'Spirit / Stealth (banyak nol)',
    16275  => 'Soulforce',
    400    => 'Accuracy',
    300    => 'Evasion',
];
foreach ($knowns as $needle => $label) {
    $packed = pack('l', $needle); // LE signed int32
    $pos = 0;
    while (($found = strpos($bin, $packed, $pos)) !== false) {
        printf("  %-15s = %-6d found at byte offset 0x%02X (field index %d)\n",
            $label, $needle, $found, intdiv($found, 4));
        $pos = $found + 1;
    }
}

// =====================================================
// 3. Decode dengan asumsi struct dari PW source
//    (versi paling umum yang beredar di komunitas)
// =====================================================
echo "\n--- Decode dengan struct asumsi (PW 1.7.x extraprop) ---\n";
$p = 0;

$fields_v1 = [
    // Kemungkinan 1: diawali version int32
    ['ep_version',        'i32'],
    ['stealth_level',     'i32'],
    ['detect_level',      'i32'],
    ['soulforce',         'i32'],
    ['spirit',            'i32'],
    ['accuracy',          'i32'],
    ['evasion',           'i32'],
    ['crit_rate',         'i32'],
    ['crit_dmg',          'i32'],
    ['atk_rank',          'i32'],
    ['def_rank',          'i32'],
    ['phys_penet',        'i32'],
    ['mag_penet',         'i32'],
];

echo "\n  [Versi 1 — version dulu, lalu stealth/detect/soul/spirit]\n";
$p = 0;
foreach ($fields_v1 as [$name, $type]) {
    $v = ri32($bin, $p);
    printf("    %-20s = %d\n", $name, $v);
}

// Kemungkinan 2: version byte, lalu langsung spirit sebelum stealth
$fields_v2 = [
    ['ep_version',        'i32'],
    ['spirit',            'i32'],
    ['stealth_level',     'i32'],
    ['detect_level',      'i32'],
    ['soulforce',         'i32'],
    ['accuracy',          'i32'],
    ['evasion',           'i32'],
    ['crit_rate',         'i32'],
    ['crit_dmg',          'i32'],
    ['atk_rank',          'i32'],
    ['def_rank',          'i32'],
    ['phys_penet',        'i32'],
    ['mag_penet',         'i32'],
];

echo "\n  [Versi 2 — version, spirit, stealth, detect, soulforce]\n";
$p = 0;
foreach ($fields_v2 as [$name, $type]) {
    $v = ri32($bin, $p);
    printf("    %-20s = %d\n", $name, $v);
}

// Kemungkinan 3: ada cuint/byte di tengah
// cuint: jika byte < 0x80 = nilai itu sendiri
//        jika byte = 0x80 = baca int32 berikutnya
//        jika byte = 0x40 = baca int32 & mask
function rcuint(string $bin, int &$p): int {
    $byte = rbyte($bin, $p);
    if ($byte == 0x80) {
        return ri32($bin, $p);
    } elseif ($byte == 0x40) {
        return ri32($bin, $p) & 0x3FFFFFFF;
    }
    return $byte;
}

echo "\n  [Versi 3 — version i32, lalu cuint fields]\n";
$p = 0;
$fields_v3 = ['ep_version','stealth_level','detect_level','soulforce','spirit','accuracy','evasion','crit_rate','crit_dmg','atk_rank','def_rank'];
$ver = ri32($bin, $p);
printf("    %-20s = %d\n", 'ep_version', $ver);
foreach (array_slice($fields_v3, 1) as $name) {
    $v = rcuint($bin, $p);
    printf("    %-20s = %d  (p=0x%02X)\n", $name, $v, $p);
}

// =====================================================
// 4. Brute-force: tampilkan semua offset yang nilainya
//    cocok dengan known values
// =====================================================
echo "\n--- Brute-force: offset mana yang = 105 (Detect Lvl) ---\n";
for ($off = 0; $off + 4 <= $len; $off++) {
    $v = unpack('l', substr($bin, $off, 4))[1];
    if ($v === 105) {
        printf("  offset 0x%02X (byte %d, field %d jika aligned) = 105\n",
            $off, $off, intdiv($off, 4));
    }
}

echo "\n--- Brute-force: offset mana yang = 16275 (Soulforce) ---\n";
for ($off = 0; $off + 4 <= $len; $off++) {
    $v = unpack('l', substr($bin, $off, 4))[1];
    if ($v === 16275) {
        printf("  offset 0x%02X (byte %d, field %d jika aligned) = 16275\n",
            $off, $off, intdiv($off, 4));
    }
}

echo "\n--- Brute-force: offset mana yang = 400 (Accuracy) ---\n";
for ($off = 0; $off + 4 <= $len; $off++) {
    $v = unpack('l', substr($bin, $off, 4))[1];
    if ($v === 400) {
        printf("  offset 0x%02X (byte %d, field %d jika aligned) = 400\n",
            $off, $off, intdiv($off, 4));
    }
}

echo "\n--- Brute-force: offset mana yang = 300 (Evasion/Hindaran) ---\n";
for ($off = 0; $off + 4 <= $len; $off++) {
    $v = unpack('l', substr($bin, $off, 4))[1];
    if ($v === 300) {
        printf("  offset 0x%02X (byte %d, field %d jika aligned) = 300\n",
            $off, $off, intdiv($off, 4));
    }
}

echo "\nDone.\n";
