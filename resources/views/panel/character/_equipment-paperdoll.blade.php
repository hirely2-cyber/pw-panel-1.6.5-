{{--
    Equipment paperdoll — uses iweb equipment-bg.png + male/female silhouette
    with absolute-positioned cells 0..31.
    Props:
      $equipment => summary['equipment']
      $gender    => 0|1
--}}
@php
    $bySlot = [];
    foreach (($equipment['items'] ?? []) as $it) {
        $bySlot[(int) $it['pos']] = $it;
    }
    $used = (int) ($equipment['used'] ?? 0);
    $genderClass = ((int) $gender) === 1 ? 'female' : 'male';
@endphp
<div class="pw-inv">
    <div class="pw-inv-header">
        <span class="pw-inv-title">Equipment</span>
        <span class="pw-inv-meta">equipped: {{ $used }}</span>
    </div>
    <div class="player__equipment {{ $genderClass }}">
        @foreach(range(0, 31) as $slot)
            @php
                $slotItem = $bySlot[$slot] ?? null;
                $slotCat  = $slotItem ? \App\Services\PW\Items\ItemCatalog::item((int) $slotItem['id']) : null;
                $slotName = $slotCat['name'] ?? ($slotItem ? '#'.$slotItem['id'] : '');
                $slotIc   = $slotItem ? \App\Services\PW\Items\ItemCatalog::iconDataUrl((int) $slotItem['id']) : '';
            @endphp
            <div class="player__equipment-item cell-{{ $slot }} pw-item-cell {{ $slotItem ? 'has-item' : 'empty' }}"
                 title="Slot {{ $slot }}{{ $slotItem ? ' — '.$slotName : '' }}"
                 @if($slotItem)
                     data-item="{{ json_encode([
                         'name'     => $slotName,
                         'id'       => $slotItem['id'],
                         'group'    => $slotCat['list'] ?? 0,
                         'index'    => basename(str_replace('\\\\', '/', $slotCat['icon'] ?? '')),
                         'guid1'    => $slotItem['guid1'],
                         'guid2'    => $slotItem['guid2'],
                         'proctype' => $slotItem['proctype'],
                         'mask'     => $slotItem['mask'],
                         'pos'      => $slotItem['pos'],
                         'expire'   => $slotItem['expire_date'],
                         'count'    => $slotItem['count'],
                         'max_count'=> $slotItem['max_count'],
                         'data'     => $slotItem['data'] ?? '',
                     ]) }}"
                     onclick="pwSelectItem(this)"
                 @endif>
                @if($slotItem && $slotIc)
                    <img src="{{ $slotIc }}" alt="{{ e($slotName) }}">
                @endif
            </div>
        @endforeach
    </div>
</div>
