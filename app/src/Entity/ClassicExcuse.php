<?php

namespace App\Entity;

use App\Repository\ClassicExcuseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ClassicExcuseRepository::class)]
class ClassicExcuse extends Excuse
{
    #[ORM\Column(nullable: true)]
    private ?int $estimatedDelay = null;

    #[ORM\Column]
    private ?bool $isReusable = null;

    public function getEstimatedDelay(): ?int
    {
        return $this->estimatedDelay;
    }

    public function setEstimatedDelay(?int $estimatedDelay): static
    {
        $this->estimatedDelay = $estimatedDelay;

        return $this;
    }

    public function isReusable(): ?bool
    {
        return $this->isReusable;
    }

    public function setIsReusable(bool $isReusable): static
    {
        $this->isReusable = $isReusable;

        return $this;
    }
}
