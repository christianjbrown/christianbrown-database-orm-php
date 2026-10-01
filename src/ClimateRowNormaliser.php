<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use function array_key_exists;
use function is_numeric;
use function is_scalar;

/**
 * Turns a raw bucket row into the typed shape. It is defensive: a non-scalar date
 * or non-numeric hour falls back to '' / 0 rather than casting an unexpected
 * type. A row without a `bucket_hour` column (daily) yields a null hour.
 */
final class ClimateRowNormaliser implements ClimateRowNormaliserInterface
{
    /**
     * @param array<string, mixed> $row
     *
     * @return array{date: string, hour: null|int, minTemperature: null|float, maxTemperature: null|float, minHumidity: null|float, maxHumidity: null|float}
     */
    public function normalise(array $row): array
    {
        return [
            'date' => self::toStringValue($row['bucket_date']),
            'hour' => array_key_exists('bucket_hour', $row) ? self::toIntValue($row['bucket_hour']) : null,
            'minTemperature' => self::toFloatOrNull($row['min_temperature']),
            'maxTemperature' => self::toFloatOrNull($row['max_temperature']),
            'minHumidity' => self::toFloatOrNull($row['min_humidity']),
            'maxHumidity' => self::toFloatOrNull($row['max_humidity']),
        ];
    }

    private static function toFloatOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private static function toIntValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private static function toStringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }
}
