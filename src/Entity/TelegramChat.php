<?php

namespace App\Entity;

use App\Repository\TelegramChatRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use JMS\Serializer\Annotation as Serializer;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/**
 * @ORM\Table(name="telegram.chats")
 * @ORM\Entity(repositoryClass=TelegramChatRepository::class)
 */
class TelegramChat extends AbstractEntity
{
    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $internalId;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $accessHash;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $link;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="boolean", options={"default": false})
     */
    private $isAvailable = false;

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
     * @ORM\ManyToMany(targetEntity=TelegramPhone::class, inversedBy="chats")
     */
    private $phones;

    public function __construct()
    {
        parent::__construct();

        $this->media = new ArrayCollection();
        $this->members = new ArrayCollection();
        $this->phones = new ArrayCollection();
    }

    public function getInternalId(): ?string
    {
        return $this->internalId;
    }

    public function setInternalId(string $internalId): self
    {
        $this->internalId = $internalId;

        return $this;
    }

    public function getAccessHash(): ?string
    {
        return $this->accessHash;
    }

    public function setAccessHash(string $accessHash): self
    {
        $this->accessHash = $accessHash;

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
}
