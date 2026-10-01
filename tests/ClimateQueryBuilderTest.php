<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Tests;

use ChristianBrown\Database\ClimateHistoryReader;
use ChristianBrown\Database\ClimateQueryBuilderInterface;
use ChristianBrown\Database\ClimateQueryRunnerInterface;
use ChristianBrown\Database\ClimateRowNormaliserInterface;
use ChristianBrown\Database\ClimateTableNameValidatorInterface;
use ChristianBrown\Database\DailyClimateQueryBuilder;
use ChristianBrown\Database\HourlyClimateQueryBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(DailyClimateQueryBuilder::class)]
#[CoversClass(HourlyClimateQueryBuilder::class)]
#[CoversClass(ClimateHistoryReader::class)]
final class ClimateQueryBuilderTest extends TestCase
{
    public function testDailyGroupsByDateOnly(): void
    {
        $sql = (new DailyClimateQueryBuilder())->build('smartthings_climate');

        self::assertStringContainsString('FROM `smartthings_climate`', $sql);
        self::assertStringContainsString('GROUP BY bucket_date ORDER BY bucket_date ASC', $sql);
        self::assertStringNotContainsString('bucket_hour', $sql);
    }

    public function testHourlyAddsTheHourBucket(): void
    {
        $sql = (new HourlyClimateQueryBuilder())->build('met_office_weather');

        self::assertStringContainsString('FROM `met_office_weather`', $sql);
        self::assertStringContainsString('HOUR(recorded_at) AS bucket_hour', $sql);
        self::assertStringContainsString('GROUP BY bucket_date, bucket_hour ORDER BY bucket_date ASC, bucket_hour ASC', $sql);
    }

    /**
     * A resolution is just a key in the injected map, so a new one needs no change
     * to the reader.
     *
     * @throws Exception
     */
    public function testReaderUsesTheBuilderRegisteredForTheResolution(): void
    {
        $builder = self::createMock(ClimateQueryBuilderInterface::class);
        $builder->expects(self::once())->method('build')->with('some_table')->willReturn('SELECT weekly');
        $queryRunner = self::createMock(ClimateQueryRunnerInterface::class);
        $queryRunner->expects(self::once())->method('fetchAll')->with('SELECT weekly', '2026-07-20 00:00:00', '2026-07-27 00:00:00')->willReturn([]);

        $reader = new ClimateHistoryReader(
            ['weekly' => $builder],
            $queryRunner,
            self::createStub(ClimateTableNameValidatorInterface::class),
            self::createStub(ClimateRowNormaliserInterface::class)
        );

        self::assertSame([], $reader->read('some_table', 'weekly', new DateTimeImmutable('2026-07-20'), new DateTimeImmutable('2026-07-27')));
    }
}
