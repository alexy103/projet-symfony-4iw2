<?php

namespace App\Entity;

use App\Repository\ProfessionalExcuseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProfessionalExcuseRepository::class)]
class ProfessionalExcuse extends Excuse
{
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $targetRecipient = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $professionalTone = null;

    public function getTargetRecipient(): ?string
    {
        return $this->targetRecipient;
    }

    public function setTargetRecipient(?string $targetRecipient): static
    {
        $this->targetRecipient = $targetRecipient;

        return $this;
    }

    public function getProfessionalTone(): ?string
    {
        return $this->professionalTone;
    }

    public function setProfessionalTone(?string $professionalTone): static
    {
        $this->professionalTone = $professionalTone;

        return $this;
    }
}
