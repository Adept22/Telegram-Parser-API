<?php

namespace App\Entity;

use App\Repository\TelegramChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.chats")
 * @ORM\Entity(repositoryClass=TelegramChatRepository::class)
 */
class TelegramChat extends AbstractEntity
{
    /**
     * @ORM\Column(type="bigint", unique=true, nullable=true)
     */
    private $internalId;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $link;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAvailable = true;

    /**
     * @ORM\OneToMany(targetEntity=TelegramChatMedia::class, mappedBy="chat")
     * 
     * @Serializer\Exclude
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity=TelegramChatMember::class, mappedBy="chat", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity=TelegramMessage::class, mappedBy="chat", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $messages;

    /**
     * @ORM\ManyToMany(targetEntity=TelegramPhone::class, inversedBy="chats")
     * @ORM\JoinTable(name="telegram_chat_telegram_phone",
     *      joinColumns={
     *          @ORM\JoinColumn(name="telegram_chat_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="telegram_phone_id", referencedColumnName="id")
     *      }
     * )
     * 
     * @Serializer\MaxDepth(2)
     */
    private $phones;

    /**
     * @ORM\ManyToMany(targetEntity=TelegramPhone::class)
     * @ORM\JoinTable(name="telegram_chat_available_telegram_phone",
     *      joinColumns={
     *          @ORM\JoinColumn(name="telegram_chat_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="telegram_phone_id", referencedColumnName="id")
     *      }
     * )
     * 
     * @Serializer\MaxDepth(2)
     */
    private $availablePhones;

    public function __construct()
    {
        parent::__construct();

        $this->media = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->phones = new ArrayCollection();
        $this->availablePhones = new ArrayCollection();
    }

    public function getInternalId(): ?int
    {
        return $this->internalId;
    }

    public function setInternalId(int $internalId): self
    {
        $this->internalId = $internalId;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

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

    public function getIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    /**
     * @return Collection|TelegramChatMedia[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(TelegramChatMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setChat($this);
        }

        return $this;
    }

    public function removeMedium(TelegramChatMedia $medium): self
    {
        if ($this->media->removeElement($medium)) {
            // set the owning side to null (unless already changed)
            if ($medium->getChat() === $this) {
                $medium->setChat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TelegramChatMember[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(TelegramChatMember $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->setChat($this);
        }

        return $this;
    }

    public function removeMember(TelegramChatMember $member): self
    {
        if ($this->members->removeElement($member)) {
            // set the owning side to null (unless already changed)
            if ($member->getChat() === $this) {
                $member->setChat(null);
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
            $message->setChat($this);
        }

        return $this;
    }

    public function removeMessage(TelegramMessage $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChat() === $this) {
                $message->setChat(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TelegramPhone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(TelegramPhone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->addChat($this);
        }

        return $this;
    }

    public function removePhone(TelegramPhone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            $phone->removeChat($this);
        }

        return $this;
    }

    /**
     * @return Collection|TelegramPhone[]
     */
    public function getAvailablePhones(): Collection
    {
        return $this->availablePhones;
    }

    public function addAvailablePhone(TelegramPhone $availablePhone): self
    {
        if (!$this->availablePhones->contains($availablePhone)) {
            $this->availablePhones[] = $availablePhone;
            $availablePhone->addChat($this);
        }

        return $this;
    }

    public function removeAvailablePhone(TelegramPhone $availablePhone): self
    {
        if ($this->availablePhones->removeElement($availablePhone)) {
            $availablePhone->removeChat($this);
        }

        return $this;
    }
}
