<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\MessageMediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.messages_medias")
 * @ORM\Entity(repositoryClass=MessageMediaRepository::class)
 */
class MessageMedia extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=Message::class, inversedBy="media")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(2)
     */
    private $message;

    /**
     * @ORM\Column(type="bigint", unique=true)
     */
    private $internalId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    public function getMessage(): ?Message
    {
        return $this->message;
    }

    public function setMessage(?Message $message): self
    {
        $this->message = $message;

        return $this;
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
