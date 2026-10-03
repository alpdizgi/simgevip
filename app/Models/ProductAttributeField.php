<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductAttributeField extends Model
{
    use HasFactory;

    public const LEGACY_KEYS = [
        'pattern',
        'thickness',
        'length',
        'fabric_content',
        'fabric_type',
        'lining',
        'collar_type',
        'sleeve_type',
        'sleeve_length',
        'closure_type',
        'pocket',
        'season',
    ];

    protected $fillable = [
        'key',
        'label',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * @return list<array{key: string, label: string}>
     */
    public static function orderedFields(): array
    {
        return static::query()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['key', 'label'])
            ->map(fn (self $field) => [
                'key' => (string) $field->key,
                'label' => (string) $field->label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, key: string, label: string, sort_order: int}>
     */
    public static function formRows(): array
    {
        return static::query()
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get(['id', 'key', 'label', 'sort_order'])
            ->map(fn (self $field) => [
                'id' => $field->id,
                'key' => $field->key,
                'label' => $field->label,
                'sort_order' => $field->sort_order,
            ])
            ->values()
            ->all();
    }

    public static function makeKeyFromLabel(string $label): string
    {
        $key = Str::slug($label, '_');

        if ($key === '') {
            $key = 'ozellik';
        }

        if (! preg_match('/^[a-z]/', $key)) {
            $key = 'a_' . $key;
        }

        $base = $key;
        $i = 2;

        while (static::query()->where('key', $key)->exists()) {
            $key = $base . '_' . $i;
            $i++;
        }

        return $key;
    }

    public static function renameInProducts(string $oldKey, string $newKey): void
    {
        if ($oldKey === $newKey) {
            return;
        }

        DB::table('products')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($oldKey, $newKey) {
                foreach ($products as $product) {
                    $values = json_decode($product->attribute_values ?? '[]', true) ?: [];

                    if (! array_key_exists($oldKey, $values)) {
                        continue;
                    }

                    $value = $values[$oldKey];
                    unset($values[$oldKey]);

                    if (! array_key_exists($newKey, $values)) {
                        $values[$newKey] = $value;
                    }

                    $update = ['attribute_values' => json_encode($values)];

                    if (in_array($oldKey, self::LEGACY_KEYS, true)) {
                        $update[$oldKey] = null;
                    }

                    if (in_array($newKey, self::LEGACY_KEYS, true)) {
                        $update[$newKey] = $value;
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update($update);
                }
            });
    }

    public static function removeFromProducts(string $key): void
    {
        DB::table('products')
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($key) {
                foreach ($products as $product) {
                    $values = json_decode($product->attribute_values ?? '[]', true) ?: [];

                    if (! array_key_exists($key, $values)) {
                        if (in_array($key, self::LEGACY_KEYS, true) && filled($product->{$key} ?? null)) {
                            DB::table('products')
                                ->where('id', $product->id)
                                ->update([$key => null]);
                        }

                        continue;
                    }

                    unset($values[$key]);

                    $update = ['attribute_values' => json_encode($values)];

                    if (in_array($key, self::LEGACY_KEYS, true)) {
                        $update[$key] = null;
                    }

                    DB::table('products')
                        ->where('id', $product->id)
                        ->update($update);
                }
            });
    }

    /**
     * @return array<string, string>
     */
    public static function displayPairsFor(Product $product): array
    {
        $values = $product->attribute_values ?? [];

        if (! is_array($values)) {
            $values = [];
        }

        $pairs = [];

        foreach (static::orderedFields() as $field) {
            $value = $values[$field['key']] ?? $product->getAttribute($field['key']);

            if ($value === null || $value === '') {
                continue;
            }

            $pairs[$field['label']] = (string) $value;
        }

        return $pairs;
    }
}
