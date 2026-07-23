<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A recorded average house temperature / humidity sample from the SmartThings
 * climate function.
 */
#[ORM\Entity]
#[ORM\Table(name: 'smartthings_climate')]
#[ORM\Index(name: 'idx_recorded_at', columns: ['recorded_at', 'temperature', 'humidity'])]
// A Doctrine entity is deliberately non-final (proxy/lazy hydration), so the
// "abstract or final" rule is suppressed for this class only.
// phpcs:disable SlevomatCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal
class SmartThingsClimate extends AbstractClimateReading
{
}
// phpcs:enable SlevomatCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal
