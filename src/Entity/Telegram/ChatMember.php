<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Repository\Telegram\ChatMemberRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(
 *  name="telegram.chats_members", 
 *  uniqueConstraints={
 *      @UniqueConstraint(name="chat_member_unique", 
 *      columns={"chat_id", "member_id"})
 *  }
 * )
 * @ORM\Entity(repositoryClass=ChatMemberRepository::class)
 */
class ChatMember extends AbstractEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=Chat::class, inversedBy="members")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(2)
     */
    private $chat;

    /**
     * @ORM\ManyToOne(targetEntity=Member::class, inversedBy="chats", cascade={"all"})
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\MaxDepth(2)
     */
    private $member;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isLeft = false;

    /**
     * @ORM\OneToMany(targetEntity=ChatMemberRole::class, mappedBy="member", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $roles;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="member", orphanRemoval=true)
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

    public function getChat(): ?Chat
    {
        return $this->chat;
    }

    public function setChat(?Chat $chat): self
    {
        $this->chat = $chat;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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
     * @return Collection|ChatMemberRole[]
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    public function addRole(ChatMemberRole $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
            $role->setMember($this);
        }

        return $this;
    }

    public function removeRole(ChatMemberRole $role): self
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
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setMember($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
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
