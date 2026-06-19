<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ExcuseCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ExcuseCategoryRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/v1/entities/excuse-categories'),
        new Get(uriTemplate: '/v1/entities/excuse-categories/{id}', requirements: ['id' => '\\d+']),
    ],
    normalizationContext: ['groups' => ['entity:reference:read']]
)]
class ExcuseCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['entity:reference:read', 'entity:excuse:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['entity:reference:read', 'entity:excuse:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['entity:reference:read'])]
    private ?string $description = null;

    /**
     * @var Collection<int, Excuse>
     */
    #[ORM\OneToMany(targetEntity: Excuse::class, mappedBy: 'category')]
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
            $excus->setCategory($this);
        }

        return $this;
    }

    public function removeExcus(Excuse $excus): static
    {
        if ($this->excuses->removeElement($excus)) {
            // set the owning side to null (unless already changed)
            if ($excus->getCategory() === $this) {
                $excus->setCategory(null);
            }
        }

        return $this;
    }
}
