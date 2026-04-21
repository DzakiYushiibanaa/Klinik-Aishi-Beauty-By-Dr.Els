<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CodeGenerator
{
    /**
     * Format: PREFIX-YYYYMM-XXXX (reset per bulan)
     * Contoh: DR-202512-0001
     */
    public static function monthly(Model $model, string $column, string $prefix, ?Carbon $now = null): string
    {
        $now ??= now();
        $ym = $now->format('Ym'); // YYYYMM

        // cari terakhir untuk bulan ini
        $latest = $model->newQuery()
            ->where($column, 'like', $prefix . '-' . $ym . '-%')
            ->orderByDesc($model->getKeyName())
            ->value($column);

        $next = 1;

        if ($latest) {
            // ambil 4 digit terakhir
            $lastNumber = (int) substr($latest, -4);
            $next = $lastNumber + 1;
        }

        return sprintf('%s-%s-%04d', $prefix, $ym, $next);
    }

    /**
     * Format: PREFIX-000001 (6 digit default)
     * Contoh: NM-000001, RM-000001
     */
    public static function prefixNumber(
        Model $model,
        string $column,
        string $prefix,
        int $digits = 6,
        string $separator = '-'
    ): string {
        $like = $prefix . $separator . str_repeat('_', $digits);

        // Ambil semua key dengan awalan ini utk membuang yang korup/teks seperti NM-OL1
        $allKeys = $model->newQuery()
            ->where($column, 'like', $prefix . $separator . '%')
            ->pluck($column);

        $max = 0;
        $prefixLen = strlen($prefix) + strlen($separator);

        foreach ($allKeys as $val) {
            $numStr = substr((string) $val, $prefixLen);
            if (is_numeric($numStr)) {
                $max = max($max, (int) $numStr);
            }
        }

        $next = $max + 1;

        return $prefix . $separator . str_pad((string) $next, $digits, '0', STR_PAD_LEFT);
    }
}
