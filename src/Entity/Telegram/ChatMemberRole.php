<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\ChatMemberRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.chats_members_roles")
 * @ORM\Entity(repositoryClass=ChatMemberRoleRepository::class)
 */
class ChatMemberRole extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=ChatMember::class, inversedBy="roles", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(2)
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

    public function getMember(): ?ChatMember
    {
        return $this->member;
    }

    public function setMember(?ChatMember $member): self
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
