<?php

namespace App\Entity\Telegram;

use App\Entity\AbstractEntity;
use App\Entity\Export;
use App\Repository\Telegram\ChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="telegram.chats")
 * @ORM\Entity(repositoryClass=ChatRepository::class)
 */
class Chat extends AbstractEntity
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @Serializer\Exclude
     */
    private $systemTitle;

    /**
     * @ORM\Column(type="text", nullable=true)
     * 
     * @Serializer\Exclude
     */
    private $systemDescription;

    /**
     * @ORM\Column(type="float", nullable=true)
     * 
     * @Serializer\Exclude
     */
    private $lat;

    /**
     * @ORM\Column(type="float", nullable=true)
     * 
     * @Serializer\Exclude
     */
    private $lon;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isAvailable = true;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     */
    private $date;

    /**
     * @ORM\OneToMany(targetEntity=ChatMedia::class, mappedBy="chat")
     * 
     * @Serializer\Exclude
     */
    private $media;

    /**
     * @ORM\OneToMany(targetEntity=ChatMember::class, mappedBy="chat", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $members;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="chat", orphanRemoval=true)
     * 
     * @Serializer\Exclude
     */
    private $messages;

    /**
     * @ORM\Column(type="datetimetz", nullable=true)
     * 
     * @Serializer\Exclude
     */
    private $lastMessageDate;

    /**
     * @ORM\OneToOne(targetEntity=ChatMedia::class)
     * 
     * @Serializer\Exclude
     */
    private $lastMedia;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     * 
     * @Serializer\Exclude
     */
    private $membersCount = 0;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     * 
     * @Serializer\Exclude
     */
    private $messagesCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity=Phone::class)
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
    
    /**
     * @ORM\ManyToMany(targetEntity=Phone::class, inversedBy="chats")
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
     * @ORM\ManyToOne(targetEntity=Parser::class, inversedBy="chats")
     * @ORM\JoinColumn(nullable=false)
     * 
     * @Serializer\Exclude
     */
    private $parser;

    /**
     * @ORM\OneToMany(targetEntity=Export::class, mappedBy="chat")
     * 
     * @Serializer\Exclude
     */
    private $exports;

    public function __construct()
    {
        parent::__construct();
        $this->media = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->availablePhones = new ArrayCollection();
        $this->phones = new ArrayCollection();
        $this->exports = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getSystemTitle(): ?string
    {
        return $this->systemTitle;
    }

    public function setSystemTitle(string $systemTitle): self
    {
        $this->systemTitle = $systemTitle;

        return $this;
    }

    public function getSystemDescription(): ?string
    {
        return $this->systemDescription;
    }

    public function setSystemDescription(string $systemDescription): self
    {
        $this->systemDescription = $systemDescription;

        return $this;
    }

    public function getLat(): ?float
    {
        return $this->lat;
    }

    public function setLat(float $lat): self
    {
        $this->lat = $lat;

        return $this;
    }

    public function getLon(): ?float
    {
        return $this->lon;
    }

    public function setLon(float $lon): self
    {
        $this->lon = $lon;

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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return Collection|ChatMedia[]
     */
    public function getMedia(): Collection
    {
        return $this->media;
    }

    public function addMedium(ChatMedia $medium): self
    {
        if (!$this->media->contains($medium)) {
            $this->media[] = $medium;
            $medium->setChat($this);
        }

        return $this;
    }

    public function removeMedium(ChatMedia $medium): self
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
     * @return Collection|ChatMember[]
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(ChatMember $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
            $member->setChat($this);
        }

        return $this;
    }

    public function removeMember(ChatMember $member): self
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
            $message->setChat($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getChat() === $this) {
                $message->setChat(null);
            }
        }

        return $this;
    }

    public function getLastMessageDate(): ?\DateTimeInterface
    {
        return $this->lastMessageDate;
    }

    public function setLastMessageDate(?\DateTimeInterface $lastMessageDate): self
    {
        $this->lastMessageDate = $lastMessageDate;

        return $this;
    }

    public function getLastMedia(): ?ChatMedia
    {
        return $this->lastMedia;
    }

    public function setLastMedia(?ChatMedia $lastMedia): self
    {
        $this->lastMedia = $lastMedia;

        return $this;
    }

    public function getMembersCount(): ?int
    {
        return $this->membersCount;
    }

    public function setMembersCount(int $membersCount): self
    {
        $this->membersCount = $membersCount;

        return $this;
    }

    public function getMessagesCount(): ?int
    {
        return $this->messagesCount;
    }

    public function setMessagesCount(int $messagesCount): self
    {
        $this->messagesCount = $messagesCount;

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getAvailablePhones(): Collection
    {
        return $this->availablePhones;
    }

    public function addAvailablePhone(Phone $availablePhone): self
    {
        if (!$this->availablePhones->contains($availablePhone)) {
            $this->availablePhones[] = $availablePhone;
            $availablePhone->addChat($this);
        }

        return $this;
    }

    public function removeAvailablePhone(Phone $availablePhone): self
    {
        if ($this->availablePhones->removeElement($availablePhone)) {
            $availablePhone->removeChat($this);
        }

        return $this;
    }

    /**
     * @return Collection|Phone[]
     */
    public function getPhones(): Collection
    {
        return $this->phones;
    }

    public function addPhone(Phone $phone): self
    {
        if (!$this->phones->contains($phone)) {
            $this->phones[] = $phone;
            $phone->addChat($this);
        }

        return $this;
    }

    public function removePhone(Phone $phone): self
    {
        if ($this->phones->removeElement($phone)) {
            $phone->removeChat($this);
        }

        return $this;
    }

    public function getParser(): ?Parser
    {
        return $this->parser;
    }

    public function setParser(?Parser $parser): self
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @return Collection|Export[]
     */
    public function getExports(): Collection
    {
        return $this->exports;
    }

    public function addExport(Export $export): self
    {
        if (!$this->exports->contains($export)) {
            $this->exports[] = $export;
            $export->setChat($this);
        }

        return $this;
    }

    public function removeExport(Export $export): self
    {
        if ($this->exports->removeElement($export)) {
            // set the owning side to null (unless already changed)
            if ($export->getChat() === $this) {
                $export->setChat(null);
            }
        }

        return $this;
    }
}
