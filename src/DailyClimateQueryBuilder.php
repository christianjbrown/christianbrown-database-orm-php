<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use function sprintf;

/**
 * Per-day buckets (UTC, the stored value's own timezone).
 */
final class DailyClimateQueryBuilder implements ClimateQueryBuilderInterface
{
    public function build(string $table): string
    {
        return sprintf(
            'SELECT DATE(%1$s) AS bucket_date,'
            .' MIN(%2$s) AS min_temperature, MAX(%2$s) AS max_temperature,'
            .' MIN(%3$s) AS min_humidity, MAX(%3$s) AS max_humidity'
            .' FROM `%4$s`'
            .' WHERE %1$s >= :start AND %1$s < :end'
            .' GROUP BY bucket_date'
            .' ORDER BY bucket_date ASC',
            self::COLUMN_RECORDED_AT,
            self::COLUMN_TEMPERATURE,
            self::COLUMN_HUMIDITY,
            $table
        );
    }
}
