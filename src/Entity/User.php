<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getUsers", "getInvitations", "getEvents"])]
    private ?int $id = null;

    #[ORM\Column(length: 55)]
    #[Groups(["getUsers", "getInvitations", "getEvents"])]
    private ?string $username = null;

    #[ORM\Column(length: 55, nullable: true)]
    #[Groups(["getUsers"])]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    #[Groups(["getUsers"])]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(["getUsers"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getUsers"])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'organizer', targetEntity: Event::class)]
    #[Groups(["getUsers"])]
    private Collection $eventsOrganized;

    #[ORM\OneToMany(mappedBy: 'inviteur', targetEntity: Invitation::class)]
    #[Groups(["getUsers"])]
    private Collection $invitationsSent;

    #[ORM\OneToMany(mappedBy: 'invitee', targetEntity: Invitation::class)]
    #[Groups(["getUsers"])]
    private Collection $invitationReceived;

    public function __construct()
    {
        $this->eventsOrganized = new ArrayCollection();
        $this->invitationsSent = new ArrayCollection();
        $this->invitationReceived = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

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

    /**
     * @return Collection<int, Event>
     */
    public function getEventsOrganized(): Collection
    {
        return $this->eventsOrganized;
    }

    public function addEventsOrganized(Event $eventsOrganized): static
    {
        if (!$this->eventsOrganized->contains($eventsOrganized)) {
            $this->eventsOrganized->add($eventsOrganized);
            $eventsOrganized->setOrganizer($this);
        }

        return $this;
    }

    public function removeEventsOrganized(Event $eventsOrganized): static
    {
        if ($this->eventsOrganized->removeElement($eventsOrganized)) {
            // set the owning side to null (unless already changed)
            if ($eventsOrganized->getOrganizer() === $this) {
                $eventsOrganized->setOrganizer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitationsSent(): Collection
    {
        return $this->invitationsSent;
    }

    public function addInvitationsSent(Invitation $invitationsSent): static
    {
        if (!$this->invitationsSent->contains($invitationsSent)) {
            $this->invitationsSent->add($invitationsSent);
            $invitationsSent->setInviteur($this);
        }

        return $this;
    }

    public function removeInvitationsSent(Invitation $invitationsSent): static
    {
        if ($this->invitationsSent->removeElement($invitationsSent)) {
            // set the owning side to null (unless already changed)
            if ($invitationsSent->getInviteur() === $this) {
                $invitationsSent->setInviteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Invitation>
     */
    public function getInvitationReceived(): Collection
    {
        return $this->invitationReceived;
    }

    public function addInvitationReceived(Invitation $invitationReceived): static
    {
        if (!$this->invitationReceived->contains($invitationReceived)) {
            $this->invitationReceived->add($invitationReceived);
            $invitationReceived->setInvitee($this);
        }

        return $this;
    }

    public function removeInvitationReceived(Invitation $invitationReceived): static
    {
        if ($this->invitationReceived->removeElement($invitationReceived)) {
            // set the owning side to null (unless already changed)
            if ($invitationReceived->getInvitee() === $this) {
                $invitationReceived->setInvitee(null);
            }
        }

        return $this;
    }
}
