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
class TelegramChatMedia
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     *
     * @Serializer\Type("uuid")
     * 
     * @var UuidInterface
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TelegramChat::class, inversedBy="media")
     */
    private $chat;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="datetimetz", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
