<?php

namespace App\Entity;

use App\Repository\EmergencyExcuseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmergencyExcuseRepository::class)]
class EmergencyExcuse extends Excuse
{
    #[ORM\Column]
    private ?int $emergencyLevel = null;

    #[ORM\Column]
    private ?bool $requiresProof = null;

    public function getEmergencyLevel(): ?int
    {
        return $this->emergencyLevel;
    }

    public function setEmergencyLevel(int $emergencyLevel): static
    {
        $this->emergencyLevel = $emergencyLevel;

        return $this;
    }

    public function isRequiresProof(): ?bool
    {
        return $this->requiresProof;
    }

    public function setRequiresProof(bool $requiresProof): static
    {
        $this->requiresProof = $requiresProof;

        return $this;
    }
}
