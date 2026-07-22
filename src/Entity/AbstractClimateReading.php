<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractClimateReading implements ClimateReadingInterface
{
    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $humidity = null;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'recorded_at', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?DateTimeImmutable $recordedAt = null;

    #[ORM\Column(type: Types::FLOAT, nullable: true)]
    private ?float $temperature = null;

    public function getHumidity(): ?float
    {
        return $this->humidity;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecordedAt(): ?DateTimeImmutable
    {
        return $this->recordedAt;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function setHumidity(?float $humidity): self
    {
        $this->humidity = $humidity;

        return $this;
    }

    public function setRecordedAt(?DateTimeImmutable $recordedAt): self
    {
        $this->recordedAt = $recordedAt;

        return $this;
    }

    public function setTemperature(?float $temperature): self
    {
        $this->temperature = $temperature;

        return $this;
    }
}
