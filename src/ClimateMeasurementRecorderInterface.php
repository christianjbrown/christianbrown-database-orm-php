<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use ChristianBrown\Database\Entity\ClimateReadingInterface;

interface ClimateMeasurementRecorderInterface
{
    public function record(ClimateReadingInterface $reading): void;
}
