<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * @return list<string>
     */
    public static function orderedNames(): array
    {
        return static::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => (string) $name)
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string, sort_order: int}>
     */
    public static function formRows(): array
    {
        return static::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'sort_order'])
            ->map(fn (self $size) => [
                'id' => $size->id,
                'name' => $size->name,
                'sort_order' => $size->sort_order,
            ])
            ->values()
            ->all();
    }

    public static function renameInVariants(string $oldName, string $newName): void
    {
        if ($oldName === $newName) {
            return;
        }

        DB::table('product_variants')
            ->orderBy('id')
            ->chunkById(100, function ($variants) use ($oldName, $newName) {
                foreach ($variants as $variant) {
                    $sizes = json_decode($variant->sizes ?? '[]', true) ?: [];

                    if (! array_key_exists($oldName, $sizes)) {
                        continue;
                    }

                    $stock = $sizes[$oldName];
                    unset($sizes[$oldName]);

                    if (! array_key_exists($newName, $sizes)) {
                        $sizes[$newName] = $stock;
                    }

                    DB::table('product_variants')
                        ->where('id', $variant->id)
                        ->update(['sizes' => json_encode($sizes)]);
                }
            });
    }

    public static function removeFromVariants(string $name): void
    {
        DB::table('product_variants')
            ->orderBy('id')
            ->chunkById(100, function ($variants) use ($name) {
                foreach ($variants as $variant) {
                    $sizes = json_decode($variant->sizes ?? '[]', true) ?: [];

                    if (! array_key_exists($name, $sizes)) {
                        continue;
                    }

                    unset($sizes[$name]);

                    DB::table('product_variants')
                        ->where('id', $variant->id)
                        ->update(['sizes' => json_encode($sizes)]);
                }
            });
    }
}
