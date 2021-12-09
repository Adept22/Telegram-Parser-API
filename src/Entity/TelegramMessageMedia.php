<?php

namespace App\Entity;

use App\Repository\TelegramMessageMediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.messages_medias")
 * @ORM\Entity(repositoryClass=TelegramMessageMediaRepository::class)
 */
class TelegramMessageMedia
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
     * @ORM\ManyToOne(targetEntity=TelegramMessage::class, inversedBy="media")
     * @ORM\JoinColumn(nullable=false)
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getMessage(): ?TelegramMessage
    {
        return $this->message;
    }

    public function setMessage(?TelegramMessage $message): self
    {
        $this->message = $message;

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
