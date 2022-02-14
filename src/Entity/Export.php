<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\ExportRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Table(name="app.export")
 * @ORM\Entity(repositoryClass=ExportRepository::class)
 */
class Export extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    public function __construct()
    {
        parent::__construct();
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
