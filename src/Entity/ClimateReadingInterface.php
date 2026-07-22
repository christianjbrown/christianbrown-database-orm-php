<?php

declare(strict_types=1);

namespace ChristianBrown\Database\Entity;

use DateTimeImmutable;

/**
 * A single climate sample: temperature and/or relative humidity captured at a
 * point in time. Persisted append-only, one row per sample.
 */
interface ClimateReadingInterface
{
    public function getHumidity(): ?float;

    public function getId(): ?int;

    public function getRecordedAt(): ?DateTimeImmutable;

    public function getTemperature(): ?float;

    public function setHumidity(?float $humidity): self;

    public function setRecordedAt(?DateTimeImmutable $recordedAt): self;

    public function setTemperature(?float $temperature): self;
}
