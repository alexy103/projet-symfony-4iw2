<?php

namespace App\Entity;

use App\Repository\ExcuseToneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExcuseToneRepository::class)]
class ExcuseTone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?int $riskLevel = null;

    /**
     * @var Collection<int, Excuse>
     */
    #[ORM\OneToMany(targetEntity: Excuse::class, mappedBy: 'tone')]
    private Collection $excuses;

    public function __construct()
    {
        $this->excuses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRiskLevel(): ?int
    {
        return $this->riskLevel;
    }

    public function setRiskLevel(int $riskLevel): static
    {
        $this->riskLevel = $riskLevel;

        return $this;
    }

    /**
     * @return Collection<int, Excuse>
     */
    public function getExcuses(): Collection
    {
        return $this->excuses;
    }

    public function addExcus(Excuse $excus): static
    {
        if (!$this->excuses->contains($excus)) {
            $this->excuses->add($excus);
            $excus->setTone($this);
        }

        return $this;
    }

    public function removeExcus(Excuse $excus): static
    {
        if ($this->excuses->removeElement($excus)) {
            // set the owning side to null (unless already changed)
            if ($excus->getTone() === $this) {
                $excus->setTone(null);
            }
        }

        return $this;
    }
}
