{{-- Server info strip (HOST / CPU / LOAD AVG / DISK / UPTIME) --}}
@php
    /** @var array $sysInfo */
    $loadColor = match (true) {
        ($sysInfo['load'][1] ?? 0) >= ($sysInfo['cpu_cores'] ?? 1) * 2 => 'text-rose-500',
        ($sysInfo['load'][1] ?? 0) >= ($sysInfo['cpu_cores'] ?? 1)     => 'text-amber-500',
        default                                                        => 'text-emerald-600',
    };
    $diskColor = match (true) {
        ($sysInfo['disk_pct'] ?? 0) >= 90 => 'text-rose-500',
        ($sysInfo['disk_pct'] ?? 0) >= 75 => 'text-amber-500',
        default                           => 'text-[var(--color-text)]',
    };
@endphp
<div class="pw-card mb-4">
    <div class="pw-card-body !py-3">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            <div>
                <div class="text-[10px] tracking-widest font-semibold text-[var(--color-text-soft)]">HOST</div>
                <div class="font-semibold text-[var(--color-text)] truncate">{{ $sysInfo['host'] }}</div>
                <div class="text-[11px] text-[var(--color-text-soft)] truncate">{{ $sysInfo['os'] }}</div>
            </div>
            <div>
                <div class="text-[10px] tracking-widest font-semibold text-[var(--color-text-soft)]">CPU</div>
                <div class="font-semibold text-[var(--color-text)]">{{ $sysInfo['cpu_cores'] }} Core</div>
                <div class="text-[11px] text-[var(--color-text-soft)] truncate" title="{{ $sysInfo['cpu_model'] }}">
                    {{ \Illuminate\Support\Str::limit($sysInfo['cpu_model'], 32) }}
                </div>
            </div>
            <div>
                <div class="text-[10px] tracking-widest font-semibold text-[var(--color-text-soft)]">LOAD AVG</div>
                <div class="font-semibold {{ $loadColor }}">{{ number_format($sysInfo['load'][1], 2) }}</div>
                <div class="text-[11px] text-[var(--color-text-soft)]">
                    5m: {{ number_format($sysInfo['load'][5], 2) }} · 15m: {{ number_format($sysInfo['load'][15], 2) }}
                </div>
            </div>
            <div>
                <div class="text-[10px] tracking-widest font-semibold text-[var(--color-text-soft)]">DISK (/)</div>
                <div class="font-semibold {{ $diskColor }}">
                    {{ $sysInfo['disk_used'] }}G <span class="text-[var(--color-text-soft)] font-normal">/ {{ $sysInfo['disk_total'] }}G</span>
                </div>
                <div class="mt-1 h-1 bg-slate-100 rounded overflow-hidden">
                    <div class="h-full bg-sky-500" style="width: {{ $sysInfo['disk_pct'] }}%"></div>
                </div>
                <div class="text-[11px] text-[var(--color-text-soft)] mt-0.5">{{ $sysInfo['disk_pct'] }}% used</div>
            </div>
            <div>
                <div class="text-[10px] tracking-widest font-semibold text-[var(--color-text-soft)]">UPTIME</div>
                <div class="font-semibold text-[var(--color-text)]">{{ $sysInfo['uptime_human'] }}</div>
                <div class="text-[11px] text-[var(--color-text-soft)]">system uptime</div>
            </div>
        </div>
    </div>
</div>
