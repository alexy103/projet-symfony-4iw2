<?php

namespace App\Entity;

use App\Repository\ExcuseValidationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExcuseValidationRepository::class)]
class ExcuseValidation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'validations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Excuse $excuse = null;

    #[ORM\ManyToOne(inversedBy: 'excuseValidations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $validator = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $validatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getExcuse(): ?Excuse
    {
        return $this->excuse;
    }

    public function setExcuse(?Excuse $excuse): static
    {
        $this->excuse = $excuse;

        return $this;
    }

    public function getValidator(): ?User
    {
        return $this->validator;
    }

    public function setValidator(?User $validator): static
    {
        $this->validator = $validator;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }
}
