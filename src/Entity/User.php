<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[UniqueEntity(fields: ['username'], message: 'There is already an account with this username')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Chat::class)]
    private Collection $chats;

    #[ORM\OneToMany(mappedBy: 'receipient', targetEntity: Chat::class)]
    private Collection $receipientChats;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: ChatMessage::class)]
    private Collection $chatMessages;

    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Your first name can only contain letters, numbers and underscores')]
    #[ORM\Column(length: 255)]
    private ?string $firstname = null;

    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Your last name can only contain letters, numbers and underscores')]
    #[ORM\Column(length: 255)]
    private ?string $lastname = null;

    #[Assert\Regex(pattern: '/^[a-zA-Z0-9_]+$/', message: 'Your username can only contain letters, numbers and underscores')]
    #[Assert\Regex(pattern: '/^[^_].*$/', message: 'Your username cannot start with an underscore')]
    #[ORM\Column(length: 30, unique: true)]
    private ?string $username = null;

    #[ORM\Column]
    private ?bool $verified = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $updated_at = null;

    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: MessageRequest::class)]
    private Collection $messageRequests;

    #[ORM\Column(length: 255)]
    private ?string $language = null;

    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: Violation::class)]
    private Collection $violations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $biography = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $profilePicture = null;

    public function __construct()
    {
        $this->chats = new ArrayCollection();
        $this->receipientChats = new ArrayCollection();
        $this->chatMessages = new ArrayCollection();
        $this->messageRequests = new ArrayCollection();
        $this->violations = new ArrayCollection();
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

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Chat>
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chat $chat): static
    {
        if (!$this->chats->contains($chat)) {
            $this->chats->add($chat);
            $chat->setCreator($this);
        }

        return $this;
    }

    public function removeChat(Chat $chat): static
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getCreator() === $this) {
                $chat->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Chat>
     */
    public function getReceipientChats(): Collection
    {
        return $this->receipientChats;
    }

    public function addReceipientChat(Chat $receipientChat): static
    {
        if (!$this->receipientChats->contains($receipientChat)) {
            $this->receipientChats->add($receipientChat);
            $receipientChat->setReceipient($this);
        }

        return $this;
    }

    public function removeReceipientChat(Chat $receipientChat): static
    {
        if ($this->receipientChats->removeElement($receipientChat)) {
            // set the owning side to null (unless already changed)
            if ($receipientChat->getReceipient() === $this) {
                $receipientChat->setReceipient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ChatMessage>
     */
    public function getChatMessages(): Collection
    {
        return $this->chatMessages;
    }

    public function addChatMessage(ChatMessage $chatMessage): static
    {
        if (!$this->chatMessages->contains($chatMessage)) {
            $this->chatMessages->add($chatMessage);
            $chatMessage->setCreator($this);
        }

        return $this;
    }

    public function removeChatMessage(ChatMessage $chatMessage): static
    {
        if ($this->chatMessages->removeElement($chatMessage)) {
            // set the owning side to null (unless already changed)
            if ($chatMessage->getCreator() === $this) {
                $chatMessage->setCreator(null);
            }
        }

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): static
    {
        $this->verified = $verified;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeImmutable $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    /**
     * @return Collection<int, MessageRequest>
     */
    public function getMessageRequests(): Collection
    {
        return $this->messageRequests;
    }

    public function addMessageRequest(MessageRequest $messageRequest): static
    {
        if (!$this->messageRequests->contains($messageRequest)) {
            $this->messageRequests->add($messageRequest);
            $messageRequest->setCreator($this);
        }

        return $this;
    }

    public function removeMessageRequest(MessageRequest $messageRequest): static
    {
        if ($this->messageRequests->removeElement($messageRequest)) {
            // set the owning side to null (unless already changed)
            if ($messageRequest->getCreator() === $this) {
                $messageRequest->setCreator(null);
            }
        }

        return $this;
    }

    public function getLanguage(): ?string
    {
        return empty($this->language) ? "en" : $this->language;
    }

    public function setLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return Collection<int, Violation>
     */
    public function getViolations(): Collection
    {
        return $this->violations;
    }

    public function addViolation(Violation $violation): static
    {
        if (!$this->violations->contains($violation)) {
            $this->violations->add($violation);
            $violation->setRecipient($this);
        }

        return $this;
    }

    public function removeViolation(Violation $violation): static
    {
        if ($this->violations->removeElement($violation)) {
            // set the owning side to null (unless already changed)
            if ($violation->getRecipient() === $this) {
                $violation->setRecipient(null);
            }
        }

        return $this;
    }

    public function getBiography(): ?string
    {
        return $this->biography;
    }

    public function setBiography(?string $biography): static
    {
        $this->biography = $biography;

        return $this;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): static
    {
        $this->profilePicture = $profilePicture;

        return $this;
    }
}
