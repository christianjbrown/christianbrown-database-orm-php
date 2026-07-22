<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Tests\Entity;

use ChristianBrown\Database\Entity\RefreshToken;
use ChristianBrown\KeyValueStore\AbstractDatabaseKeyValueStoreEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RefreshToken::class)]
final class RefreshTokenTest extends TestCase
{
    public function test(): void
    {
        $refreshToken = new RefreshToken();
        self::assertInstanceOf(AbstractDatabaseKeyValueStoreEntity::class, $refreshToken);
    }
}
