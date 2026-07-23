<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Tests;

use ChristianBrown\Database\ClimateHistoryReader;
use ChristianBrown\Database\ClimateHistoryReaderInterface;
use ChristianBrown\Database\ClimateQueryRunnerInterface;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClimateHistoryReader::class)]
final class ClimateHistoryReaderTest extends TestCase
{
    /**
     * The normaliser is defensive: a non-scalar date or non-numeric hour falls
     * back to '' / 0 rather than casting an unexpected type.
     *
     * @throws Exception
     */
    public function testNormalisationIsDefensiveAgainstUnexpectedValueTypes(): void
    {
        $queryRunner = self::createStub(ClimateQueryRunnerInterface::class);
        $queryRunner->method('fetchAll')->willReturn([
            ['bucket_date' => [], 'bucket_hour' => 'not-numeric', 'min_temperature' => null, 'max_temperature' => null, 'min_humidity' => null, 'max_humidity' => null],
        ]);

        $reader = new ClimateHistoryReader($queryRunner);
        $actual = $reader->read('smartthings_climate', ClimateHistoryReaderInterface::RESOLUTION_HOURLY, new DateTimeImmutable('2026-07-20'), new DateTimeImmutable('2026-07-21'));

        self::assertSame([
            ['date' => '', 'hour' => 0, 'minTemperature' => null, 'maxTemperature' => null, 'minHumidity' => null, 'maxHumidity' => null],
        ], $actual);
    }

    /**
     * @throws Exception
     */
    public function testReadDailyNormalisesRowsWithNullBuckets(): void
    {
        $queryRunner = self::createStub(ClimateQueryRunnerInterface::class);
        $queryRunner->method('fetchAll')->willReturn([
            ['bucket_date' => '2026-07-20', 'min_temperature' => '18.5', 'max_temperature' => '24.8', 'min_humidity' => '40.0', 'max_humidity' => '55.25'],
            // A bucket where temperature was never recorded (only humidity): min/max temp are null.
            ['bucket_date' => '2026-07-21', 'min_temperature' => null, 'max_temperature' => null, 'min_humidity' => '50', 'max_humidity' => '60'],
        ]);

        $reader = new ClimateHistoryReader($queryRunner);
        $actual = $reader->read('smartthings_climate', ClimateHistoryReaderInterface::RESOLUTION_DAILY, new DateTimeImmutable('2026-07-20'), new DateTimeImmutable('2026-07-22'));

        self::assertSame([
            ['date' => '2026-07-20', 'hour' => null, 'minTemperature' => 18.5, 'maxTemperature' => 24.8, 'minHumidity' => 40.0, 'maxHumidity' => 55.25],
            ['date' => '2026-07-21', 'hour' => null, 'minTemperature' => null, 'maxTemperature' => null, 'minHumidity' => 50.0, 'maxHumidity' => 60.0],
        ], $actual);
    }

    /**
     * @throws Exception
     */
    public function testReadHourlyIncludesTheHour(): void
    {
        $queryRunner = self::createStub(ClimateQueryRunnerInterface::class);
        $queryRunner->method('fetchAll')->willReturn([
            ['bucket_date' => '2026-07-20', 'bucket_hour' => '14', 'min_temperature' => '20', 'max_temperature' => '22', 'min_humidity' => '45', 'max_humidity' => '48'],
        ]);

        $reader = new ClimateHistoryReader($queryRunner);
        $actual = $reader->read('met_office_weather', ClimateHistoryReaderInterface::RESOLUTION_HOURLY, new DateTimeImmutable('2026-07-20'), new DateTimeImmutable('2026-07-21'));

        self::assertSame([
            ['date' => '2026-07-20', 'hour' => 14, 'minTemperature' => 20.0, 'maxTemperature' => 22.0, 'minHumidity' => 45.0, 'maxHumidity' => 48.0],
        ], $actual);
    }

    /**
     * @throws Exception
     */
    public function testReadRejectsAnInvalidTableName(): void
    {
        $reader = new ClimateHistoryReader(self::createStub(ClimateQueryRunnerInterface::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid table name: not a table');

        $reader->read('not a table', ClimateHistoryReaderInterface::RESOLUTION_DAILY, new DateTimeImmutable('2026-07-20'), new DateTimeImmutable('2026-07-21'));
    }

    /**
     * @throws Exception
     */
    public function testReadRejectsAnUnknownResolution(): void
    {
        $reader = new ClimateHistoryReader(self::createStub(ClimateQueryRunnerInterface::class));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid resolution: weekly');

        $reader->read('smartthings_climate', 'weekly', new DateTimeImmutable('2026-07-20'), new DateTimeImmutable('2026-07-21'));
    }
}
