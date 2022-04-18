<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\ChatPhoneRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(
 *  name="telegram.chats_phones", 
 *  uniqueConstraints={
 *      @ORM\UniqueConstraint(name="chat_phone_unique", 
 *      columns={"chat_id", "phone_id"})
 *  }
 * )
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
     * @ORM\ManyToOne(targetEntity=Phone::class, inversedBy="chats")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(1)
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isUsing = false;

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;

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

    public function getIsUsing(): ?bool
    {
        return $this->isUsing;
    }

    public function setIsUsing(bool $isUsing): self
    {
        $this->isUsing = $isUsing;

        return $this;
    }
}
