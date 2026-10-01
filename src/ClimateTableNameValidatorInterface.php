<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use InvalidArgumentException;

interface ClimateTableNameValidatorInterface
{
    /**
     * @throws InvalidArgumentException when the name is not a bare identifier
     */
    public function assertValid(string $table): void;
}
