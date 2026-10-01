<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use InvalidArgumentException;

use function preg_match;
use function sprintf;

/**
 * A table name cannot be bound as a parameter and is interpolated into the SQL,
 * so it is validated as a bare identifier even though it comes from our own
 * entity metadata and never from request input.
 */
final class ClimateTableNameValidator implements ClimateTableNameValidatorInterface
{
    public function assertValid(string $table): void
    {
        if (1 !== preg_match('/^[A-Za-z0-9_]+$/', $table)) {
            throw new InvalidArgumentException(sprintf('Invalid table name: %s', $table));
        }
    }
}
