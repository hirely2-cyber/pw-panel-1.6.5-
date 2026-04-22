{{--
    Single item cell — 32x32 icon + optional count badge.
    Props:
      $item   => ['id','pos','count','max_count','proctype','expire_date','guid1','guid2','mask']
      $size   => px (default 32)
--}}
@php
    $size = $size ?? 32;
    $cat  = $item ? \App\Services\PW\Items\ItemCatalog::item((int) $item['id']) : null;
    $name = $cat['name'] ?? ('#'.($item['id'] ?? 0));
    $icon = $item ? \App\Services\PW\Items\ItemCatalog::iconDataUrl((int) $item['id']) : '';
    $count = (int) ($item['count'] ?? 0);
@endphp
<div class="pw-item-cell {{ $item ? 'has-item' : 'empty' }}"
     style="width:{{ $size }}px;height:{{ $size }}px"
     @if($item)
         title="{{ $name }} (#{{ $item['id'] }}) ×{{ $count }}"
         data-item="{{ json_encode([
             'name'     => $name,
             'id'       => $item['id'],
             'group'    => $cat['list'] ?? 0,
             'index'    => basename(str_replace('\\', '/', $cat['icon'] ?? '')),
             'guid1'    => $item['guid1'],
             'guid2'    => $item['guid2'],
             'proctype' => $item['proctype'],
             'mask'     => $item['mask'],
             'pos'      => $item['pos'],
             'expire'   => $item['expire_date'],
             'count'    => $item['count'],
             'max_count'=> $item['max_count'],
             'data'     => $item['data'] ?? '',
         ]) }}"
         onclick="pwSelectItem(this)"
     @endif>
    @if($item && $icon)
        <img src="{{ $icon }}" alt="{{ e($name) }}" loading="lazy">
        @if($count > 1)
            <span class="pw-item-count">{{ $count > 9999 ? number_format($count) : $count }}</span>
        @endif
    @endif
</div>
