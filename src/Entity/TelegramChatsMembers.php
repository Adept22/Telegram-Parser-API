<?php

namespace App\Entity;

use App\Repository\TelegramChatsMembersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.chats_members")
 * @ORM\Entity(repositoryClass=TelegramChatsMembersRepository::class)
 */
class TelegramChatsMembers
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
     * @ORM\ManyToOne(targetEntity=TelegramChat::class, inversedBy="members")
     * @ORM\JoinColumn(nullable=false)
     */
    private $chat;

    /**
     * @ORM\ManyToOne(targetEntity=TelegramMember::class, inversedBy="chats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $member;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLeft;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=TelegramChatsMembersRoles::class, mappedBy="member", orphanRemoval=true)
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity=TelegramMessage::class, mappedBy="member", orphanRemoval=true)
     */
    private $messages;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->messages = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection|TelegramChatsMembersRoles[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(TelegramChatsMembersRoles $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setMember($this);
        }

        return $this;
    }

    public function removeRole(TelegramChatsMembersRoles $role): self
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
