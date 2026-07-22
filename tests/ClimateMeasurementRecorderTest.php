<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Tests;

use ChristianBrown\Database\ClimateMeasurementRecorder;
use ChristianBrown\Database\Entity\ClimateReadingInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClimateMeasurementRecorder::class)]
final class ClimateMeasurementRecorderTest extends TestCase
{
    public function testRecordPersistsAndFlushes(): void
    {
        $reading = self::createStub(ClimateReadingInterface::class);

        $entityManager = self::createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with($reading);
        $entityManager->expects(self::once())->method('flush');

        $recorder = new ClimateMeasurementRecorder($entityManager);
        $recorder->record($reading);
    }
}
