<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

interface ClimateQueryRunnerInterface
{
    /**
     * Runs a climate range query bound with `:start` and `:end`.
     *
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql, string $start, string $end): array;
}
