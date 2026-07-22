<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use ChristianBrown\Database\Entity\ClimateReadingInterface;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Persists a single climate reading (append-only). Callers are expected to wrap
 * `record()` in their own try/catch so a database failure never disturbs the
 * function's HTTP response.
 */
final class ClimateMeasurementRecorder implements ClimateMeasurementRecorderInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function record(ClimateReadingInterface $reading): void
    {
        $this->entityManager->persist($reading);
        $this->entityManager->flush();
    }
}
