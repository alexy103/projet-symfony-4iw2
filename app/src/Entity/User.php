<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $passwordHash = null;

    /**
     * @var Collection<int, Excuse>
     */
    #[ORM\OneToMany(targetEntity: Excuse::class, mappedBy: 'author')]
    private Collection $excuses;

    /**
     * @var Collection<int, ExcuseComment>
     */
    #[ORM\OneToMany(targetEntity: ExcuseComment::class, mappedBy: 'author')]
    private Collection $excuseComments;

    /**
     * @var Collection<int, ExcuseRating>
     */
    #[ORM\OneToMany(targetEntity: ExcuseRating::class, mappedBy: 'author')]
    private Collection $excuseRatings;

    /**
     * @var Collection<int, ExcuseValidation>
     */
    #[ORM\OneToMany(targetEntity: ExcuseValidation::class, mappedBy: 'validator')]
    private Collection $excuseValidations;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user')]
    private Collection $notifications;

    /**
     * @var Collection<int, Badge>
     */
    #[ORM\ManyToMany(targetEntity: Badge::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_badge')]
    private Collection $badges;

    public function __construct()
    {
        $this->excuses = new ArrayCollection();
        $this->excuseComments = new ArrayCollection();
        $this->excuseRatings = new ArrayCollection();
        $this->excuseValidations = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->badges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->passwordHash;
    }

    public function getPasswordHash(): ?string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): static
    {
        $this->passwordHash = $passwordHash;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0passwordHash"] = hash('crc32c', $this->passwordHash);

        return $data;
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
            $excus->setAuthor($this);
        }

        return $this;
    }

    public function removeExcus(Excuse $excus): static
    {
        if ($this->excuses->removeElement($excus)) {
            // set the owning side to null (unless already changed)
            if ($excus->getAuthor() === $this) {
                $excus->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExcuseComment>
     */
    public function getExcuseComments(): Collection
    {
        return $this->excuseComments;
    }

    public function addExcuseComment(ExcuseComment $excuseComment): static
    {
        if (!$this->excuseComments->contains($excuseComment)) {
            $this->excuseComments->add($excuseComment);
            $excuseComment->setAuthor($this);
        }

        return $this;
    }

    public function removeExcuseComment(ExcuseComment $excuseComment): static
    {
        if ($this->excuseComments->removeElement($excuseComment)) {
            // set the owning side to null (unless already changed)
            if ($excuseComment->getAuthor() === $this) {
                $excuseComment->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExcuseRating>
     */
    public function getExcuseRatings(): Collection
    {
        return $this->excuseRatings;
    }

    public function addExcuseRating(ExcuseRating $excuseRating): static
    {
        if (!$this->excuseRatings->contains($excuseRating)) {
            $this->excuseRatings->add($excuseRating);
            $excuseRating->setAuthor($this);
        }

        return $this;
    }

    public function removeExcuseRating(ExcuseRating $excuseRating): static
    {
        if ($this->excuseRatings->removeElement($excuseRating)) {
            // set the owning side to null (unless already changed)
            if ($excuseRating->getAuthor() === $this) {
                $excuseRating->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ExcuseValidation>
     */
    public function getExcuseValidations(): Collection
    {
        return $this->excuseValidations;
    }

    public function addExcuseValidation(ExcuseValidation $excuseValidation): static
    {
        if (!$this->excuseValidations->contains($excuseValidation)) {
            $this->excuseValidations->add($excuseValidation);
            $excuseValidation->setValidator($this);
        }

        return $this;
    }

    public function removeExcuseValidation(ExcuseValidation $excuseValidation): static
    {
        if ($this->excuseValidations->removeElement($excuseValidation)) {
            // set the owning side to null (unless already changed)
            if ($excuseValidation->getValidator() === $this) {
                $excuseValidation->setValidator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (!$this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->setUser($this);
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            // set the owning side to null (unless already changed)
            if ($notification->getUser() === $this) {
                $notification->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Badge>
     */
    public function getBadges(): Collection
    {
        return $this->badges;
    }

    public function addBadge(Badge $badge): static
    {
        if (!$this->badges->contains($badge)) {
            $this->badges->add($badge);
        }

        return $this;
    }

    public function removeBadge(Badge $badge): static
    {
        $this->badges->removeElement($badge);

        return $this;
    }
}
