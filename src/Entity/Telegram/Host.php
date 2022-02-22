<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\HostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.hosts")
 * @ORM\Entity(repositoryClass=HostRepository::class)
 */
class Host extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $publicIp;

    /**
     * @ORM\Column(type="string", length=15, unique=true)
     */
    private $localIp;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity=Parser::class, mappedBy="host")
     * 
     * @Serializer\Exclude
     */
    private $parsers;

    public function __construct()
    {
        parent::__construct();
        
        $this->parsers = new ArrayCollection();
    }

    public function getPublicIp(): ?string
    {
        return $this->publicIp;
    }

    public function setPublicIp(string $publicIp): self
    {
        $this->publicIp = $publicIp;

        return $this;
    }

    public function getLocalIp(): ?string
    {
        return $this->localIp;
    }

    public function setLocalIp(string $localIp): self
    {
        $this->localIp = $localIp;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Parser[]
     */
    public function getParsers(): Collection
    {
        return $this->parsers;
    }

    public function addParser(Parser $parser): self
    {
        if (!$this->parsers->contains($parser)) {
            $this->parsers[] = $parser;
            $parser->setHost($this);
        }

        return $this;
    }

    public function removeParser(Parser $parser): self
    {
        if ($this->parsers->removeElement($parser)) {
            // set the owning side to null (unless already changed)
            if ($parser->getHost() === $this) {
                $parser->setHost(null);
            }
        }

        return $this;
    }
}
