<?php

namespace App\Entity;

use App\Repository\TelegramChatMemberRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.chats_members_roles")
 * @ORM\Entity(repositoryClass=TelegramChatMemberRoleRepository::class)
 */
class TelegramChatMemberRole extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=TelegramChatMember::class, inversedBy="roles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $member;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    public function getMember(): ?TelegramChatMember
    {
        return $this->member;
    }

    public function setMember(?TelegramChatMember $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
