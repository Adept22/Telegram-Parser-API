<?php

namespace App\Entity;

use App\Entity\Telegram\Parser;
use App\Repository\ServerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="app.servers")
 * @ORM\Entity(repositoryClass=ServerRepository::class)
 */
class Server extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=15, unique=true)
     */
    private $ip;

    /**
     * @ORM\Column(type="integer")
     */
    private $port = 22;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     * @Serializer\Exclude
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Parser::class, mappedBy="server")
     */
    private $telegramParsers;

    public function __construct()
    {
        parent::__construct();
        $this->telegramParsers = new ArrayCollection();
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(int $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection|Parser[]
     */
    public function getTelegramParsers(): Collection
    {
        return $this->telegramParsers;
    }

    public function addTelegramParser(Parser $telegramParser): self
    {
        if (!$this->telegramParsers->contains($telegramParser)) {
            $this->telegramParsers[] = $telegramParser;
            $telegramParser->setServer($this);
        }

        return $this;
    }

    public function removeTelegramParser(Parser $telegramParser): self
    {
        if ($this->telegramParsers->removeElement($telegramParser)) {
            // set the owning side to null (unless already changed)
            if ($telegramParser->getServer() === $this) {
                $telegramParser->setServer(null);
            }
        }

        return $this;
    }
}
