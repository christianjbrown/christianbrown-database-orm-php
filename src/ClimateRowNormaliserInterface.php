<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

interface ClimateRowNormaliserInterface
{
    /**
     * @param array<string, mixed> $row a raw bucket row from the query
     *
     * @return array{date: string, hour: null|int, minTemperature: null|float, maxTemperature: null|float, minHumidity: null|float, maxHumidity: null|float}
     */
    public function normalise(array $row): array;
}
