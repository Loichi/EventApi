<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\InvitationRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InvitationRepository::class)]
#[ApiResource]
class Invitation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["getInvitations", "getUsers", "getEvents"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getInvitations"])]
    private ?string $question = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["getInvitations"])]
    private ?string $response;


    #[ORM\Column]
    #[Groups(["getInvitations"])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["getInvitations"])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'invitationsSent')]
    #[Groups(["getInvitations"])]
    private ?User $inviteur = null;

    #[ORM\ManyToOne(inversedBy: 'invitationReceived')]
    #[Groups(["getInvitations"])]
    private ?User $invitee = null;

    #[ORM\ManyToOne(inversedBy: 'invitations', cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'event_id', referencedColumnName: 'id')]
    #[Groups(["getInvitations"])]
    private ?Event $event = null;


    #[ORM\Column(nullable: true)]
    #[Groups(["getInvitations"])]
    private ?bool $accepted = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestion(): ?string
    {
        return $this->question;
    }

    public function setQuestion(?string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): self
    {
        $this->response = $response;
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

    public function getInviteur(): ?User
    {
        return $this->inviteur;
    }

    public function setInviteur(?User $inviteur): static
    {
        $this->inviteur = $inviteur;

        return $this;
    }

    public function getInvitee(): ?User
    {
        return $this->invitee;
    }

    public function setInvitee(?User $invitee): static
    {
        $this->invitee = $invitee;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }


    public function isAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(?bool $accepted): self
    {
        $this->accepted = $accepted;
        return $this;
    }
}
