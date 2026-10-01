<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

interface ClimateHistoryReaderFactoryInterface
{
    public function create(ClimateQueryRunnerInterface $queryRunner): ClimateHistoryReaderInterface;
}
