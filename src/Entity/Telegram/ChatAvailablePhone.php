<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\ChatAvailablePhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(
 *  name="telegram.chats_available_phones", 
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="chat_available_phone_unique", 
 *      columns={"chat_id", "parser_phone_id"})
 *  }
 * )
 * @ORM\Entity(repositoryClass=ChatAvailablePhoneRepository::class)
 */
class ChatAvailablePhone extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=Chat::class, inversedBy="availablePhones")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(1)
     */
    private $chat;

    /**
     * @ORM\ManyToOne(targetEntity=ParserPhone::class, inversedBy="availableForChats")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(1)
     */
    private $parserPhone;

    /**
     * @ORM\OneToOne(targetEntity=ChatPhone::class, mappedBy="availablePhone")
     * 
     * @Serializer\Exclude
     */
    private $chatPhone;

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getParserPhone(): ?ParserPhone
    {
        return $this->parserPhone;
    }

    public function setParserPhone(?ParserPhone $parserPhone): self
    {
        $this->parserPhone = $parserPhone;

        return $this;
    }

    public function getChatPhone(): ?ChatPhone
    {
        return $this->chatPhone;
    }

    public function setChatPhone(?ChatPhone $chatPhone): self
    {
        $this->chatPhone = $chatPhone;

        return $this;
    }
}
