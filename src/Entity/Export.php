<?php

namespace App\Entity;

use App\Entity\AbstractEntity;
use App\Repository\ExportRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="app.export")
 * @ORM\Entity(repositoryClass=ExportRepository::class)
 */
class Export extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=Telegram\Chat::class, inversedBy="exports")
     * 
     * @Serializer\MaxDepth(1)
     */
    private $chat;

    /**
     * @ORM\Column(type="array")
     */
    private $entities = ["members", "messages"];

    /**
     * @ORM\Column(type="json_array", nullable=true)
     */
    private $interval;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status = "created";

    public function getChat(): ?Telegram\Chat
    {
        return $this->chat;
    }

    public function setChat(?Telegram\Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getEntities(): ?array
    {
        return $this->entities;
    }

    public function setEntities(array $entities): self
    {
        $this->entities = $entities;

        return $this;
    }

    public function getInterval(): ?array
    {
        return $this->interval;
    }

    public function setInterval(array $interval): self
    {
        $this->interval = $interval;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
