@php
    /**
     * PW class badge — icon + name + race label.
     *
     * Props:
     * @var int|null $occupation role_occupation (primary)
     * @var int|null $gender     role_gender (used for disambiguation)
     * @var int|null $race       role_race (fallback only)
     * @var string   $size       'xs'|'sm'|'md'  (default sm, compact)
     * @var bool     $showName   show class name + race (default true)
     */
    $size       = $size       ?? 'sm';
    $showName   = $showName   ?? true;
    $occupation = isset($occupation) ? (int) $occupation : null;
    $gender     = isset($gender)     ? (int) $gender     : null;
    $race       = isset($race)       ? (int) $race       : null;

    // 1) gender-specific override  2) occupation  3) fallback race label only
    $class = null;
    if ($occupation !== null && $gender !== null) {
        $class = config("pw_classes.occupations_by_gender.$occupation.$gender");
    }
    if (!$class && $occupation !== null) {
        $class = config("pw_classes.occupations.$occupation");
    }

    $raceId   = $class['race']    ?? $race;
    $raceInfo = $raceId !== null ? config("pw_classes.races.$raceId") : null;

    $iconFile  = $class['icon']         ?? null;
    $className = $class['name']         ?? '—';
    $raceName  = $raceInfo['name']      ?? 'Unknown';
    $raceColor = $raceInfo['color']     ?? '#64748b';

    $dims = match ($size) {
        'xs' => 'w-6 h-6',
        'md' => 'w-9 h-9',
        'lg' => 'w-11 h-11',
        default => 'w-7 h-7',
    };
@endphp

<div class="inline-flex items-center gap-2 min-w-0">
    <div class="shrink-0 {{ $dims }} rounded bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center ring-1 ring-slate-200/60"
         title="{{ $className }} · {{ $raceName }}">
        @if ($iconFile)
            <img src="{{ asset('images/icon/'.$iconFile) }}"
                 alt="{{ $className }}"
                 class="w-full h-full object-cover"
                 loading="lazy">
        @else
            <span class="text-[10px] text-slate-400">?</span>
        @endif
    </div>
    @if ($showName)
        <div class="leading-tight min-w-0">
            <div class="text-[13px] font-semibold truncate">{{ $className }}</div>
            <div class="text-[10px] font-medium uppercase tracking-wide truncate"
                 style="color: {{ $raceColor }}">{{ $raceName }}</div>
        </div>
    @endif
</div>
