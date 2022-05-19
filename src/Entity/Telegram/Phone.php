<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\PhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Table(name="telegram.phones")
 * @ORM\Entity(repositoryClass=PhoneRepository::class)
 */
class Phone extends AbstractEntity
{
    /**
     * @ORM\Column(type="bigint", unique=true, nullable=true)
     */
    private $internalId;

    /**
     * @ORM\Column(type="text", unique=true, nullable=true)
     */
    private $session;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $number;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lastName;

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
     * @ORM\ManyToOne(targetEntity=Parser::class, inversedBy="phones")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\Exclude
     */
    private $parser;

    /**
     * @ORM\OneToMany(targetEntity=ChatPhone::class, mappedBy="phone", cascade={"remove"}, orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $chats;

    public function __construct()
    {
        parent::__construct();
        
        $this->chats = new ArrayCollection();
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

    public function getSession(): ?string
    {
        return $this->session;
    }

    public function setSession(string $session): self
    {
        $this->session = $session;

        return $this;
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

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

    public function getParser(): ?Parser
    {
        return $this->parser;
    }

    public function setParser(?Parser $parser): self
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @return Collection|ChatPhone[]
     */
    public function getChatPhones(): Collection
    {
        return $this->chats;
    }

    public function addChatPhone(ChatPhone $chat): self
    {
        if (!$this->chats->contains($chat)) {
            $this->chats[] = $chat;
        }

        return $this;
    }

    public function removeChatPhone(ChatPhone $chat): self
    {
        $this->chats->removeElement($chat);

        return $this;
    }
}
