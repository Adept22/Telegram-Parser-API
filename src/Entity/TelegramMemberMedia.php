<?php

namespace App\Entity;

use App\Repository\TelegramMemberMediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.members_medias")
 * @ORM\Entity(repositoryClass=TelegramMemberMediaRepository::class)
 */
class TelegramMemberMedia extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=TelegramMember::class, inversedBy="media")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(2)
     */
    private $member;

    /**
     * @ORM\Column(type="bigint", unique=true)
     */
    private $internalId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    public function getMember(): ?TelegramMember
    {
        return $this->member;
    }

    public function setMember(?TelegramMember $member): self
    {
        $this->member = $member;

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
