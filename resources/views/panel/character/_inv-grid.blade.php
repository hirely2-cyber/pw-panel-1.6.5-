{{--
    Container grid — renders a flex grid of 32x32 cells.
    Props:
      $title     => 'Inventory'
      $container => summary['pocket'|'storehouse'|...] → ['capacity','used','items']
      $cols      => columns per row (default 8)
      $small     => caption under title
--}}
@php
    $cols = $cols ?? 8;
    $cap  = (int) ($container['capacity'] ?? 0);
    $used = (int) ($container['used'] ?? 0);
    $bySlot = [];
    foreach (($container['items'] ?? []) as $it) {
        $bySlot[(int) $it['pos']] = $it;
    }
@endphp
<div class="pw-inv">
    <div class="pw-inv-header">
        <span class="pw-inv-title">{{ $title }}</span>
        <span class="pw-inv-meta">cells: {{ $cap }} · used: {{ $used }}</span>
    </div>
    @if($cap > 0)
        <div class="pw-inv-grid" style="grid-template-columns: repeat({{ $cols }}, 34px)">
            @for($i = 0; $i < $cap; $i++)
                @include('panel.character._item-cell', ['item' => $bySlot[$i] ?? null])
            @endfor
        </div>
    @else
        <div class="pw-inv-empty">— not allocated —</div>
    @endif
</div>
