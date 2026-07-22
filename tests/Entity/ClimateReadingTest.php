<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Tests\Entity;

use ChristianBrown\Database\Entity\AbstractClimateReading;
use ChristianBrown\Database\Entity\ClimateReadingInterface;
use ChristianBrown\Database\Entity\MetOfficeWeather;
use ChristianBrown\Database\Entity\SmartThingsClimate;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractClimateReading::class)]
final class ClimateReadingTest extends TestCase
{
    /**
     * @return array<int, array{0: ClimateReadingInterface}>
     */
    public static function dataProvider(): array
    {
        return [
            [new SmartThingsClimate()],
            [new MetOfficeWeather()],
        ];
    }

    #[DataProvider('dataProvider')]
    public function test(ClimateReadingInterface $entity): void
    {
        self::assertNull($entity->getId());
        self::assertNull($entity->getRecordedAt());
        self::assertNull($entity->getTemperature());
        self::assertNull($entity->getHumidity());

        $recordedAt = new DateTimeImmutable('2026-07-22 14:00:00');
        $entity->setRecordedAt($recordedAt);
        $entity->setTemperature(20.5);
        $entity->setHumidity(48.25);

        self::assertNull($entity->getId());
        self::assertSame($recordedAt, $entity->getRecordedAt());
        self::assertSame(20.5, $entity->getTemperature());
        self::assertSame(48.25, $entity->getHumidity());
    }
}
