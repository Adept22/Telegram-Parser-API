<?php

namespace App\Entity;

use App\Repository\TelegramPhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="telegram.phones")
 * @ORM\Entity(repositoryClass=TelegramPhoneRepository::class)
 */
class TelegramPhone extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstName;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isVerified = false;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isBanned = false;
    
    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     */
    private $code;
    
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codeHash;

    /**
     * @ORM\ManyToMany(targetEntity=TelegramChat::class, mappedBy="phones")
     * 
     * @Serializer\Exclude
     */
    private $chats;

    public function __construct()
    {
        parent::__construct();
        
        $this->chats = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

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

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): self
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getIsBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): self
    {
        $this->isBanned = $isBanned;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCodeHash(): ?string
    {
        return $this->codeHash;
    }

    public function setCodeHash(?string $codeHash): self
    {
        $this->codeHash = $codeHash;

        return $this;
    }

    /**
     * @return Collection|TelegramChat[]
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(TelegramChat $chat): self
    {
        if (!$this->chats->contains($chat)) {
            $this->chats[] = $chat;
        }

        return $this;
    }

    public function removeChat(TelegramChat $chat): self
    {
        $this->chats->removeElement($chat);

        return $this;
    }
    
    /**
     * @Serializer\VirtualProperty()
     */
    public function getChatsCount()
    {
        return $this->getChats()->count();
    }
}
