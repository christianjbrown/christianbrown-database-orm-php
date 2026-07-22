<?php

declare(strict_types=1);

namespace ChristianBrown\Database;

use Doctrine\ORM\EntityManagerInterface;

interface EntityManagerFactoryInterface
{
    public function getEntityManager(): EntityManagerInterface;
}
