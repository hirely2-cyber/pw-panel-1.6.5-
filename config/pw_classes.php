<?php

/*
|--------------------------------------------------------------------------
| Perfect World — Occupation, Class & Race definitions
|--------------------------------------------------------------------------
|
| The `role_occupation` byte (a.k.a. `cls`) directly determines the class,
| and the class implies race + gender. Mapping confirmed from iweb's
| "starting characters" chars.html (cls0gender0 ... cls14gender0).
|
| Icons: /public/images/icon/<file>.png
|
*/

return [

    'races' => [
        0 => ['name' => 'Human',       'color' => '#f97316'],
        1 => ['name' => 'Winged Elf',  'color' => '#38bdf8'],
        2 => ['name' => 'Untamed',     'color' => '#84cc16'],
        3 => ['name' => 'Tideborn',    'color' => '#6366f1'],
        4 => ['name' => 'Earthguard',  'color' => '#eab308'],
        5 => ['name' => 'Nightshade',  'color' => '#ec4899'],
    ],

    // Primary lookup: by role_occupation (cls). Gender-locked for base classes.
    'occupations' => [
        0  => ['name' => 'Blademaster',  'icon' => 'blademaster.png',  'race' => 0, 'gender' => 0],
        1  => ['name' => 'Wizard',       'icon' => 'wizzard.png',      'race' => 0, 'gender' => 1],
        2  => ['name' => 'Psychic',      'icon' => 'psychic.png',      'race' => 3, 'gender' => 0],
        3  => ['name' => 'Venomancer',   'icon' => 'venomancer.png',   'race' => 2, 'gender' => 1],
        4  => ['name' => 'Barbarian',    'icon' => 'barbarian.png',    'race' => 2, 'gender' => 0],
        5  => ['name' => 'Assassin',     'icon' => 'assasin.png',      'race' => 3, 'gender' => 1],
        6  => ['name' => 'Archer',       'icon' => 'archer.png',       'race' => 1, 'gender' => 0],
        7  => ['name' => 'Cleric',       'icon' => 'cleric.png',       'race' => 1, 'gender' => 1],
        8  => ['name' => 'Seeker',       'icon' => 'seeker.png',       'race' => 4, 'gender' => 0],
        9  => ['name' => 'Mystic',       'icon' => 'mystic.png',       'race' => 4, 'gender' => 1],
        10 => ['name' => 'Duskblade',    'icon' => 'duskblade.png',    'race' => 5, 'gender' => 0],
        11 => ['name' => 'Stormbringer', 'icon' => 'stormbringer.png', 'race' => 5, 'gender' => 1],
        12 => ['name' => 'Technician',   'icon' => 'technician.png',   'race' => 1, 'gender' => 1],
        13 => ['name' => 'Edgerunner',   'icon' => 'edgerunner.png',   'race' => 1, 'gender' => 0],
        14 => ['name' => 'Wildwalker',   'icon' => 'wildwalker.png',   'race' => 2, 'gender' => 0],
        15 => ['name' => 'Bard',         'icon' => 'bard.png',         'race' => 0, 'gender' => 0],
        16 => ['name' => 'Kosa',         'icon' => 'kosa.png',         'race' => 0, 'gender' => 1],
    ],

    // Expansion overrides: [occupation][gender] => {...}. Used when a cls byte
    // means different classes depending on gender (e.g. v1.6.5: cls6gender0=Archer,
    // cls6gender1=Technician). Fill in when applicable for your version.
    'occupations_by_gender' => [
        // 6 => [1 => ['name' => 'Technician', 'icon' => 'technician.png', 'race' => 1, 'gender' => 1]],
        // 7 => [0 => ['name' => 'Edgerunner', 'icon' => 'edgerunner.png', 'race' => 1, 'gender' => 0]],
    ],

    /*
     | Cultivation stages — mapped from `role.status.level2` binary field.
     | Array-of-arrays format allows duplicate values (sage vs demon at same tier).
     | Each entry: ['v' => level2_value, 'n' => display_name]
     */
    'cultivations' => [
        // Basic cultivations — stored as sequential int (0–8), same across all PW 1.x versions
        ['v' => 0, 'n' => 'Spiritual Initiate'],
        ['v' => 1, 'n' => 'Spiritual Adept'],
        ['v' => 2, 'n' => 'Aware of Principle'],
        ['v' => 3, 'n' => 'Aware of Harmony'],
        ['v' => 4, 'n' => 'Aware of Discord'],
        ['v' => 5, 'n' => 'Aware of Coalescence'],
        ['v' => 6, 'n' => 'Transcendent'],
        ['v' => 7, 'n' => 'Enlightened One'],
        ['v' => 8, 'n' => 'Aware of Vacuity'],
        // Sage path — old format (PW 1.3.x / 1.5.x)
        ['v' => 20, 'n' => 'Aware of the Myriad (Sage 1)'],
        ['v' => 21, 'n' => 'Master of Harmony (Sage 2)'],
        ['v' => 22, 'n' => 'Celestial Sage (Sage 3)'],
        // Demon path — old format (PW 1.3.x / 1.5.x)
        ['v' => 30, 'n' => 'Aware of the Void (Demon 1)'],
        ['v' => 31, 'n' => 'Master of Discord (Demon 2)'],
        ['v' => 32, 'n' => 'Celestial Demon (Demon 3)'],
        // Sage path — PW 1.7.4 format
        ['v' => 276, 'n' => 'Aware of the Myriad 1.7.4 (Sage 1)'],
        ['v' => 277, 'n' => 'Master of Harmony 1.7.4 (Sage 2)'],
        ['v' => 278, 'n' => 'Celestial Sage 1.7.4 (Sage 3)'],
        // Demon path — PW 1.7.4 format
        ['v' => 542, 'n' => 'Aware of the Void 1.7.4 (Demon 1)'],
        ['v' => 543, 'n' => 'Master of Discord 1.7.4 (Demon 2)'],
        ['v' => 544, 'n' => 'Celestial Demon 1.7.4 (Demon 3)'],
    ],

    /*
     | Vigor Points (max_ap / Chi drops). 4 tiers only, from iweb lang::$max_ap.
     */
    'vigor_points' => [
        0   => '0 — Without Chi',
        199 => '199 — 1 Drop',
        299 => '299 — 2 Drops',
        399 => '399 — 3 Drops',
    ],
];
