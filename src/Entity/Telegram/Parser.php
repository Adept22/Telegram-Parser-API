<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\ParserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.parsers")
 * @ORM\Entity(repositoryClass=ParserRepository::class)
 */
class Parser extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status = "created";

    /**
     * @ORM\Column(type="integer")
     */
    private $api_id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $api_hash;

    /**
     * @ORM\ManyToOne(targetEntity=Host::class, inversedBy="parsers", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $host;

    /**
     * @ORM\OneToMany(targetEntity=Chat::class, mappedBy="parser", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $chats;

    /**
     * @ORM\OneToMany(targetEntity=Phone::class, mappedBy="parser", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $phones;

    public function __construct()
    {
        parent::__construct();
        
        $this->chats = new ArrayCollection();
        $this->phones = new ArrayCollection();
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getApiId(): ?int
    {
        return $this->api_id;
    }

    public function setApiId(int $api_id): self
    {
        $this->api_id = $api_id;

        return $this;
    }

    public function getApiHash(): ?string
    {
        return $this->api_hash;
    }

    public function setApiHash(string $api_hash): self
    {
        $this->api_hash = $api_hash;

        return $this;
    }

    public function getHost(): ?Host
    {
        return $this->host;
    }

    public function setHost(?Host $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->setParser($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            // set the owning side to null (unless already changed)
            if ($phone->getParser() === $this) {
                $phone->setParser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Chat[]
     */
    public function getChats(): Collection
    {
        return $this->chats;
    }

    public function addChat(Chat $chat): self
    {
        if (!$this->chats->contains($chat)) {
            $this->chats[] = $chat;
            $chat->setParser($this);
        }

        return $this;
    }

    public function removeChat(Chat $chat): self
    {
        if ($this->chats->removeElement($chat)) {
            // set the owning side to null (unless already changed)
            if ($chat->getParser() === $this) {
                $chat->setParser(null);
            }
        }

        return $this;
    }
}
