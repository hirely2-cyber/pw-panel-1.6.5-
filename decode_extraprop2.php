<?php
/**
 * Decode extraprop — compare 2 karakter
 *
 * Char1 (Wukong1, Lv105 Wukong):
 *   Spirit=0, Stealth=0, Detect=105, Soulforce=16275
 *   Accuracy=400, Evasion=300, ATKRank=0, DEFRank=0
 *
 * Char2 (Lv105 Technician):
 *   Spirit=552, Soulforce=51876, Stealth=0, Detect=105
 *   Accuracy=12975, Evasion=4953, ATKRank=229, DEFRank=82
 *   PhysPenet=345, MagPenet=269
 */

$chars = [
    'C1_Wukong1' => [
        'hex'   => '07000000011469e884740000000000000000000000000201000000000003040000000a000000053c0004ec6900000000060000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000644080000001080e869108ee8695a65e8690061e969600be66900a7ee69600be66900edf3691080e869108ee8695a65e8690061e969600be66900a7ee69600be66900edf369000000080800000000000000000000000d0800000000000000000000000e0400000000',
        'known' => [
            105   => 'Detect Lvl',
            16275 => 'Soulforce',
            400   => 'Accuracy',
            300   => 'Evasion',
            0     => 'Spirit/Stealth',
        ],
    ],
    'C2_Technician' => [
        'hex'   => '080000000114695406be0000000000000000000000000001000000000003040000000a000000040e000000000000000000000000f003000000053c60683d7d0000000006000000080000000000000008000000000000000800000000000000080000000000000008000000000000000800000000000000000000064408000000b47be8690080e869b47be86990a3e869b47be86910e0ee697e2ce169904b026ab47be869a081e869b47be8691089e869b47be8691017f0697e2ce1691077076a000000080800000000000000000000000d0800000000000000000000000e0400000000',
        'known' => [
            552   => 'Spirit',
            51876 => 'Soulforce',
            105   => 'Detect Lvl',
            12975 => 'Accuracy',
            4953  => 'Evasion',
            229   => 'ATK Rank',
            82    => 'DEF Rank',
            345   => 'Phys Penet',
            269   => 'Mag Penet',
        ],
    ],
];

// Helpers
function ri32(string $bin, int &$p): int {
    if ($p + 4 > strlen($bin)) return 0;
    $r = unpack('l', substr($bin, $p, 4)); $p += 4; return $r[1];
}
function rbyte(string $bin, int &$p): int {
    if ($p + 1 > strlen($bin)) return 0;
    $r = unpack('C', substr($bin, $p, 1)); $p += 1; return $r[1];
}

// =====================================================
// 1. Brute-force cari semua known values (byte-level)
// =====================================================
echo "=== BRUTE-FORCE: cari known values di setiap offset ===\n";
foreach ($chars as $label => $info) {
    $bin = hex2bin($info['hex']);
    $len = strlen($bin);
    echo "\n--- $label (total $len bytes) ---\n";
    foreach ($info['known'] as $needle => $name) {
        if ($needle === 0) continue;
        $found = [];
        for ($off = 0; $off + 4 <= $len; $off++) {
            $v = unpack('l', substr($bin, $off, 4))[1];
            if ($v === $needle) {
                $aligned = ($off % 4 === 0) ? sprintf("[field %d]", intdiv($off, 4)) : "[NOT-aligned]";
                $found[] = sprintf("0x%02X %s", $off, $aligned);
            }
        }
        if ($found) {
            printf("  %-18s = %-7d  =>  %s\n", $name, $needle, implode(', ', $found));
        } else {
            printf("  %-18s = %-7d  =>  NOT FOUND\n", $name, $needle);
        }
    }
}

// =====================================================
// 2. Side-by-side aligned int32 dump + compare
// =====================================================
echo "\n\n=== SIDE-BY-SIDE: aligned int32 fields ===\n";
$bins = [];
foreach ($chars as $label => $info) {
    $bins[$label] = hex2bin($info['hex']);
}
$labels = array_keys($bins);
$maxF = intdiv(max(array_map('strlen', $bins)) , 4);

printf("  %-6s  %-10s  %-16s  %-16s  %s\n", 'Field', 'Offset', $labels[0], $labels[1], 'DIFF');
echo str_repeat('-', 78) . "\n";
for ($i = 0; $i < $maxF; $i++) {
    $off = $i * 4;
    $v = [];
    foreach ($bins as $label => $bin) {
        $p = $off;
        $v[] = ($off + 4 <= strlen($bin)) ? ri32($bin, $p) : '?';
    }
    $diff = ($v[0] !== $v[1]) ? '<-- DIFF' : '';
    printf("  [%02d]  0x%02X      %-16s  %-16s  %s\n", $i, $off, $v[0], $v[1], $diff);
}

// =====================================================
// 3. Ringkasan: field mana yang beda + cocok known value
// =====================================================
echo "\n\n=== RINGKASAN FIELD BERBEDA + MATCH KNOWN VALUES ===\n";
$allKnown = [];
foreach ($chars as $label => $info) {
    foreach ($info['known'] as $val => $name) {
        $allKnown[$val] = $name;
    }
}

for ($i = 0; $i < $maxF; $i++) {
    $off = $i * 4;
    $vals = [];
    foreach ($bins as $bin) {
        $p = $off;
        $vals[] = ($off + 4 <= strlen($bin)) ? ri32($bin, $p) : null;
    }
    if ($vals[0] === $vals[1]) continue;
    // Field berbeda — cek apakah salah satu match known
    $matches = [];
    foreach ($vals as $idx => $v) {
        if (isset($allKnown[$v])) {
            $matches[] = "{$allKnown[$v]}={$v} (C" . ($idx+1) . ")";
        }
    }
    $matchStr = $matches ? implode(', ', $matches) : '';
    printf("  Field[%02d] 0x%02X:  C1=%-10s  C2=%-10s  %s\n",
        $i, $off, $vals[0], $vals[1], $matchStr);
}

echo "\nDone.\n";
