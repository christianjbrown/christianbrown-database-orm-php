<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

interface ClimateQueryBuilderInterface
{
    public const string COLUMN_HUMIDITY = 'humidity';
    public const string COLUMN_RECORDED_AT = 'recorded_at';
    public const string COLUMN_TEMPERATURE = 'temperature';

    /**
     * Builds the range query for one resolution. The table name must already be
     * validated; the query is bound with `:start` and `:end`, buckets are UTC and
     * ordered earliest first.
     */
    public function build(string $table): string;
}
