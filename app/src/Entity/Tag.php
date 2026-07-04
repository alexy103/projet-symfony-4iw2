<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/v1/entities/tags'),
        new Get(uriTemplate: '/v1/entities/tags/{id}', requirements: ['id' => '\\d+']),
    ],
    normalizationContext: ['groups' => ['tag:read']]
)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['tag:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['tag:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 30, nullable: true)]
    #[Groups(['tag:read'])]
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
