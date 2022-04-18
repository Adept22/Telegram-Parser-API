<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\ParserPhoneRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(
 *  name="telegram.parsers_phones", 
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="parser_phone_unique", 
 *      columns={"parser_id", "phone_id"})
 *  }
 * )
 * @ORM\Entity(repositoryClass=ParserPhoneRepository::class)
 */
class ParserPhone extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=Parser::class, inversedBy="phones")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(1)
     */
    private $parser;

    /**
     * @ORM\OneToOne(targetEntity=Phone::class, inversedBy="parser")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(1)
     */
    private $phone;

    /**
     * @ORM\OneToMany(targetEntity=ChatPhone::class, mappedBy="parserPhone")
     * 
     * @Serializer\Exclude
     */
    private $chatPhones;

    /**
     * @ORM\OneToMany(targetEntity=ChatAvailablePhone::class, mappedBy="parserPhone")
     * 
     * @Serializer\Exclude
     */
    private $availableForChats;

    public function __construct()
    {
        parent::__construct();
        
        $this->chatPhones = new ArrayCollection();
        $this->availableForChats = new ArrayCollection();
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

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function setPhone(?Phone $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection|ChatPhone[]
     */
    public function getChatPhones(): Collection
    {
        return $this->chatPhones;
    }

    public function addChatPhone(ChatPhone $chatPhone): self
    {
        if (!$this->chatPhones->contains($chatPhone)) {
            $this->chatPhones[] = $chatPhone;
        }

        return $this;
    }

    public function removeChatPhone(ChatPhone $chatPhone): self
    {
        $this->chatPhones->removeElement($chatPhone);

        return $this;
    }

    /**
     * @return Collection|ChatAvailablePhone[]
     */
    public function getAvailableForChats(): Collection
    {
        return $this->availableForChats;
    }

    public function addAvailableForChat(ChatAvailablePhone $availableForChat): self
    {
        if (!$this->availableForChats->contains($availableForChat)) {
            $this->availableForChats[] = $availableForChat;
        }

        return $this;
    }

    public function removeAvailableForChat(ChatAvailablePhone $availableForChat): self
    {
        $this->availableForChats->removeElement($availableForChat);

        return $this;
    }
}
