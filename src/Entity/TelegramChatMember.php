<?php

namespace App\Entity;

use App\Repository\TelegramChatMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.chats_members")
 * @ORM\Entity(repositoryClass=TelegramChatMemberRepository::class)
 */
class TelegramChatMember extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=TelegramChat::class, inversedBy="members")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(2)
     */
    private $chat;

    /**
     * @ORM\ManyToOne(targetEntity=TelegramMember::class, inversedBy="chats")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(2)
     */
    private $member;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLeft = false;

    /**
     * @ORM\OneToMany(targetEntity=TelegramChatMemberRole::class, mappedBy="member", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity=TelegramMessage::class, mappedBy="member", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $messages;

    public function __construct()
    {
        parent::__construct();
        
        $this->roles = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getChat(): ?TelegramChat
    {
        return $this->chat;
    }

    public function setChat(?TelegramChat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getMember(): ?TelegramMember
    {
        return $this->member;
    }

    public function setMember(?TelegramMember $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getIsLeft(): ?bool
    {
        return $this->isLeft;
    }

    public function setIsLeft(bool $isLeft): self
    {
        $this->isLeft = $isLeft;

        return $this;
    }

    /**
     * @return Collection|TelegramChatMemberRole[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(TelegramChatMemberRole $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setMember($this);
        }

        return $this;
    }

    public function removeRole(TelegramChatMemberRole $role): self
    {
        if ($this->roles->removeElement($role)) {
            // set the owning side to null (unless already changed)
            if ($role->getMember() === $this) {
                $role->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TelegramMessage[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(TelegramMessage $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setMember($this);
        }

        return $this;
    }

    public function removeMessage(TelegramMessage $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getMember() === $this) {
                $message->setMember(null);
            }
        }

        return $this;
    }
}
