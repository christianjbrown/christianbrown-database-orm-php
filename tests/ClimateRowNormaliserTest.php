<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Tests;

use ChristianBrown\Database\ClimateRowNormaliser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClimateRowNormaliser::class)]
final class ClimateRowNormaliserTest extends TestCase
{
    public function testADailyRowHasANullHour(): void
    {
        $actual = (new ClimateRowNormaliser())->normalise(
            ['bucket_date' => '2026-07-20', 'min_temperature' => null, 'max_temperature' => null, 'min_humidity' => '50', 'max_humidity' => '60']
        );

        self::assertSame(['date' => '2026-07-20', 'hour' => null, 'minTemperature' => null, 'maxTemperature' => null, 'minHumidity' => 50.0, 'maxHumidity' => 60.0], $actual);
    }

    public function testAHourlyRowKeepsItsHour(): void
    {
        $actual = (new ClimateRowNormaliser())->normalise(
            ['bucket_date' => '2026-07-20', 'bucket_hour' => '14', 'min_temperature' => '20', 'max_temperature' => '22.5', 'min_humidity' => '45', 'max_humidity' => '48']
        );

        self::assertSame(['date' => '2026-07-20', 'hour' => 14, 'minTemperature' => 20.0, 'maxTemperature' => 22.5, 'minHumidity' => 45.0, 'maxHumidity' => 48.0], $actual);
    }

    public function testUnexpectedValueTypesFallBackToDefaults(): void
    {
        $actual = (new ClimateRowNormaliser())->normalise(
            ['bucket_date' => [], 'bucket_hour' => 'not-numeric', 'min_temperature' => 'x', 'max_temperature' => [], 'min_humidity' => null, 'max_humidity' => null]
        );

        self::assertSame(['date' => '', 'hour' => 0, 'minTemperature' => null, 'maxTemperature' => null, 'minHumidity' => null, 'maxHumidity' => null], $actual);
    }
}
