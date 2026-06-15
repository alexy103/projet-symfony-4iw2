<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $color = null;

    #[ORM\ManyToOne]
    private ?User $owner = null;

    /**
     * @var Collection<int, Excuse>
     */
    #[ORM\ManyToMany(targetEntity: Excuse::class, mappedBy: 'tags')]
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

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    public function isGlobal(): bool
    {
        return null === $this->owner;
    }

    /**
     * @return Collection<int, Excuse>
     */
    public function getExcuses(): Collection
    {
        return $this->excuses;
    }

    public function addExcuse(Excuse $excuse): static
    {
        if (!$this->excuses->contains($excuse)) {
            $this->excuses->add($excuse);
            $excuse->addTag($this);
        }

        return $this;
    }

    public function removeExcuse(Excuse $excuse): static
    {
        if ($this->excuses->removeElement($excuse)) {
            $excuse->removeTag($this);
        }

        return $this;
    }
}

