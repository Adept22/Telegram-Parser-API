<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Entity\Server;
use App\Repository\Telegram\ParserRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="telegram.parsers")
 * @ORM\Entity(repositoryClass=ParserRepository::class)
 */
class Parser extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=12)
     */
    private $containerId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $containerName;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status = "published";

    /**
     * @ORM\Column(type="integer")
     */
    private $api_id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $api_hash;

    /**
     * @ORM\ManyToOne(targetEntity=Server::class, inversedBy="telegramParsers")
     * @ORM\JoinColumn(nullable=false)
     */
    private $server;

    public function getContainerId(): ?string
    {
        return $this->containerId;
    }

    public function setContainerId(string $containerId): self
    {
        $this->containerId = $containerId;

        return $this;
    }

    public function getContainerName(): ?string
    {
        return $this->containerName;
    }

    public function setContainerName(string $containerName): self
    {
        $this->containerName = $containerName;

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

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setServer(?Server $server): self
    {
        $this->server = $server;

        return $this;
    }
}
