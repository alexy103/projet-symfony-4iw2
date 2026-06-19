<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\ExcuseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ExcuseRepository::class)]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'classic' => ClassicExcuse::class,
    'emergency' => EmergencyExcuse::class,
    'professional' => ProfessionalExcuse::class,
])]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: '/v1/entities/excuses'),
        new Get(uriTemplate: '/v1/entities/excuses/{id}', requirements: ['id' => '\\d+']),
    ],
    normalizationContext: ['groups' => ['entity:excuse:read']]
)]
abstract class Excuse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['entity:excuse:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['entity:excuse:read'])]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['entity:excuse:read'])]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Groups(['entity:excuse:read'])]
    private ?string $status = null;

    #[ORM\Column]
    #[Groups(['entity:excuse:read'])]
    private ?int $urgencyLevel = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['entity:excuse:read'])]
    private ?int $credibilityScore = null;

    #[ORM\Column]
    #[Groups(['entity:excuse:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['entity:excuse:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'excuses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $author = null;

    #[ORM\ManyToOne(inversedBy: 'excuses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['entity:excuse:read'])]
    private ?ExcuseContext $context = null;

    #[ORM\ManyToOne(inversedBy: 'excuses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['entity:excuse:read'])]
    private ?ExcuseCategory $category = null;

    #[ORM\ManyToOne(inversedBy: 'excuses')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['entity:excuse:read'])]
    private ?ExcuseTone $tone = null;

    /**
     * @var Collection<int, ExcuseComment>
     */
    #[ORM\OneToMany(targetEntity: ExcuseComment::class, mappedBy: 'excuse')]
    private Collection $comments;

    /**
     * @var Collection<int, ExcuseRating>
     */
    #[ORM\OneToMany(targetEntity: ExcuseRating::class, mappedBy: 'excuse')]
    private Collection $ratings;

    /**
     * @var Collection<int, ExcuseValidation>
     */
    #[ORM\OneToMany(targetEntity: ExcuseValidation::class, mappedBy: 'excuse')]
    private Collection $validations;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'excuses')]
    #[ORM\JoinTable(name: 'excuse_tag')]
    private Collection $tags;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->validations = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, ExcuseComment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(ExcuseComment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setExcuse($this);
        }

        return $this;
    }

    public function removeComment(ExcuseComment $comment): static
    {
        $this->comments->removeElement($comment);

        return $this;
    }

    /**
     * @return Collection<int, ExcuseRating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(ExcuseRating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setExcuse($this);
        }

        return $this;
    }

    public function removeRating(ExcuseRating $rating): static
    {
        $this->ratings->removeElement($rating);

        return $this;
    }

    /**
     * @return Collection<int, ExcuseValidation>
     */
    public function getValidations(): Collection
    {
        return $this->validations;
    }

    public function addValidation(ExcuseValidation $validation): static
    {
        if (!$this->validations->contains($validation)) {
            $this->validations->add($validation);
            $validation->setExcuse($this);
        }

        return $this;
    }

    public function removeValidation(ExcuseValidation $validation): static
    {
        $this->validations->removeElement($validation);

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
