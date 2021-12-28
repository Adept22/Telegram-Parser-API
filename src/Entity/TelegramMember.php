<?php

namespace App\Entity;

use App\Repository\TelegramMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.members")
 * @ORM\Entity(repositoryClass=TelegramMemberRepository::class)
 */
class TelegramMember extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $internalId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $about;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity=TelegramMemberMedia::class, mappedBy="member", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity=TelegramChatMember::class, mappedBy="member")
     * 
     * @Serializer\Exclude
     */
    private $chats;

    public function __construct()
    {
        parent::__construct();
        
        $this->media = new ArrayCollection();
        $this->chats = new ArrayCollection();
    }

    public function getInternalId(): ?string
    {
        return $this->internalId;
    }

    public function setInternalId(string $internalId): self
    {
        $this->internalId = $internalId;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): self
    {
        $this->about = $about;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection|TelegramMemberMedia[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(TelegramMemberMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setMember($this);
        }

        return $this;
    }

    public function removeMedium(TelegramMemberMedia $medium): self
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getMember() === $this) {
                $medium->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TelegramChatMember[]
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(TelegramChatMember $chat): self
    {
        if (!$this->chats->contains($chat)) {
            $this->chats[] = $chat;
            $chat->setMember($this);
        }

        return $this;
    }

    public function removeChat(TelegramChatMember $chat): self
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getMember() === $this) {
                $chat->setMember(null);
            }
        }

        return $this;
    }
}
