<?php

namespace App\Entity;

use App\Repository\TelegramMessageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.messages")
 * @ORM\Entity(repositoryClass=TelegramMessageRepository::class)
 */
class TelegramMessage extends AbstractEntity
{
    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private $internalId;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\ManyToOne(targetEntity=TelegramChatMember::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(1)
     */
    private $member;

    /**
     * @ORM\OneToOne(targetEntity=TelegramMessage::class, cascade={"persist", "remove"})
     * 
     * @Serializer\MaxDepth(1)
     */
    private $replyTo;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPinned = false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $forwardedFromId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $forwardedFromName;

    /**
     * @ORM\OneToMany(targetEntity=TelegramMessageMedia::class, mappedBy="message", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $media;

    public function __construct()
    {
        parent::__construct();
        
        $this->media = new ArrayCollection();
    }

    public function getInternalId(): ?int
    {
        return $this->internalId;
    }

    public function setInternalId(int $internalId): self
    {
        $this->internalId = $internalId;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getMember(): ?TelegramChatMember
    {
        return $this->member;
    }

    public function setMember(?TelegramChatMember $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getReplyTo(): ?self
    {
        return $this->replyTo;
    }

    public function setReplyTo(?self $replyTo): self
    {
        $this->replyTo = $replyTo;

        return $this;
    }

    public function getIsPinned(): ?bool
    {
        return $this->isPinned;
    }

    public function setIsPinned(bool $isPinned): self
    {
        $this->isPinned = $isPinned;

        return $this;
    }

    public function getForwardedFromId(): ?string
    {
        return $this->forwardedFromId;
    }

    public function setForwardedFromId(?string $forwardedFromId): self
    {
        $this->forwardedFromId = $forwardedFromId;

        return $this;
    }

    public function getForwardedFromName(): ?string
    {
        return $this->forwardedFromName;
    }

    public function setForwardedFromName(?string $forwardedFromName): self
    {
        $this->forwardedFromName = $forwardedFromName;

        return $this;
    }

    /**
     * @return Collection|TelegramMessageMedia[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(TelegramMessageMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setMessage($this);
        }

        return $this;
    }

    public function removeMedium(TelegramMessageMedia $medium): self
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getMessage() === $this) {
                $medium->setMessage(null);
            }
        }

        return $this;
    }
}
