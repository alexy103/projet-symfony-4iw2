<?php

namespace App\Entity;

use App\Repository\ExcuseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExcuseRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'classic' => ClassicExcuse::class,
    'emergency' => EmergencyExcuse::class,
    'professional' => ProfessionalExcuse::class,
])]
abstract class Excuse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $urgencyLevel = null;

    #[ORM\Column(nullable: true)]
    private ?int $credibilityScore = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'excuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'excuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExcuseContext $context = null;

    #[ORM\ManyToOne(inversedBy: 'excuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExcuseCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'excuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExcuseTone $tone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUrgencyLevel(): ?int
    {
        return $this->urgencyLevel;
    }

    public function setUrgencyLevel(int $urgencyLevel): static
    {
        $this->urgencyLevel = $urgencyLevel;

        return $this;
    }

    public function getCredibilityScore(): ?int
    {
        return $this->credibilityScore;
    }

    public function setCredibilityScore(?int $credibilityScore): static
    {
        $this->credibilityScore = $credibilityScore;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getContext(): ?ExcuseContext
    {
        return $this->context;
    }

    public function setContext(?ExcuseContext $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function getCategory(): ?ExcuseCategory
    {
        return $this->category;
    }

    public function setCategory(?ExcuseCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getTone(): ?ExcuseTone
    {
        return $this->tone;
    }

    public function setTone(?ExcuseTone $tone): static
    {
        $this->tone = $tone;

        return $this;
    }
}
