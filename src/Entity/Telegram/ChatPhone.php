<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\ChatPhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.chats_phones")
 * @ORM\Entity(repositoryClass=ChatPhoneRepository::class)
 */
class ChatPhone extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=Chat::class, inversedBy="phones")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(1)
     */
    private $chat;

    /**
     * @ORM\OneToOne(targetEntity=ChatAvailablePhone::class)
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\Exclude
     */
    private $availablePhone;

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getChatAvailablePhone(): ?ChatAvailablePhone
    {
        return $this->availablePhone;
    }

    public function setChatAvailablePhone(?ChatAvailablePhone $availablePhone): self
    {
        $this->availablePhone = $availablePhone;

        return $this;
    }
}
