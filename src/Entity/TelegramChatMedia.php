<?php

namespace App\Entity;

use App\Repository\TelegramChatMediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.chats_medias")
 * @ORM\Entity(repositoryClass=TelegramChatMediaRepository::class)
 */
class TelegramChatMedia extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=TelegramChat::class, inversedBy="media")
     * 
     * @Serializer\MaxDepth(1)
     */
    private $chat;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    public function getChat(): ?TelegramChat
    {
        return $this->chat;
    }

    public function setChat(?TelegramChat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
