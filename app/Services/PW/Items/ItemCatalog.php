<?php

namespace App\Services\PW\Items;

/**
 * Lookup service untuk metadata item + icon PNG.
 * Port dari iweb: items.json + icons.json (gzinflate+base64 JSON).
 *   - items.json: [itemId => ['name'=>..,'icon'=>'Surfaces/.../foo.dds','list'=>3]]
 *   - icons.json: [iconBasenameLower => base64 JPEG]
 *
 * Data di-cache in-memory per request (sekali decode ≈0.25s).
 * Pakai Laravel cache kalau perlu cross-request.
 */
class ItemCatalog
{
    private static ?array $items = null;
    private static ?array $icons = null;

    public static function items(): array
    {
        if (self::$items === null) {
            self::$items = self::loadPacked(storage_path('app/pw/items.json'));
        }
        return self::$items;
    }

    public static function icons(): array
    {
        if (self::$icons === null) {
            self::$icons = self::loadPacked(storage_path('app/pw/icons.json'));
        }
        return self::$icons;
    }

    private static function loadPacked(string $path): array
    {
        if (!is_file($path)) return [];
        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') return [];
        $inflated = @gzinflate($raw);
        if ($inflated === false) return [];
        $decoded = @base64_decode($inflated, true);
        if ($decoded === false) return [];
        $json = json_decode($decoded, true);
        return is_array($json) ? $json : [];
    }

    /**
     * Return item metadata: ['id'=>int,'name'=>string,'list'=>int,'icon'=>string].
     */
    public static function item(int $id): ?array
    {
        if ($id <= 0) return null;
        $all = self::items();
        $row = $all[$id] ?? $all[(string) $id] ?? null;
        if (!$row) return null;
        return [
            'id'   => $id,
            'name' => $row['name'] ?? ('#'.$id),
            'list' => (int) ($row['list'] ?? 0),
            'icon' => $row['icon'] ?? 'unknown.dds',
        ];
    }

    /**
     * Return base64 JPEG (sans data URL prefix) for item id, or unknown icon.
     */
    public static function iconDataFor(int $id): string
    {
        $icons = self::icons();
        if ($id > 0) {
            $meta = self::item($id);
            if ($meta) {
                $base = strtolower(basename(str_replace('\\', '/', $meta['icon'])));
                if (isset($icons[$base])) return $icons[$base];
            }
        }
        return $icons['unknown.dds'] ?? '';
    }

    public static function iconDataUrl(int $id): string
    {
        $b64 = self::iconDataFor($id);
        return $b64 === '' ? '' : 'data:image/jpeg;base64,'.$b64;
    }
}
