<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BubbleTeaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BubbleTeaRepository::class)]
#[ApiResource]
class BubbleTea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_url = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $inactivated_at = null;

    #[ORM\ManyToOne(inversedBy: 'bubbleTeas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $created_by = null;

    /**
     * @var Collection<int, StoreBubbleTea>
     */
    #[ORM\OneToMany(targetEntity: StoreBubbleTea::class, mappedBy: 'bubbleTea')]
    private Collection $storeBubbleTeas;

    public function __construct()
    {
        $this->storeBubbleTeas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->image_url;
    }

    public function setImageUrl(?string $image_url): static
    {
        $this->image_url = $image_url;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getInactivatedAt(): ?\DateTimeImmutable
    {
        return $this->inactivated_at;
    }

    public function setInactivatedAt(?\DateTimeImmutable $inactivated_at): static
    {
        $this->inactivated_at = $inactivated_at;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): static
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return Collection<int, StoreBubbleTea>
     */
    public function getStoreBubbleTeas(): Collection
    {
        return $this->storeBubbleTeas;
    }

    public function addStoreBubbleTea(StoreBubbleTea $storeBubbleTea): static
    {
        if (!$this->storeBubbleTeas->contains($storeBubbleTea)) {
            $this->storeBubbleTeas->add($storeBubbleTea);
            $storeBubbleTea->setBubbleTea($this);
        }

        return $this;
    }

    public function removeStoreBubbleTea(StoreBubbleTea $storeBubbleTea): static
    {
        if ($this->storeBubbleTeas->removeElement($storeBubbleTea)) {
            // set the owning side to null (unless already changed)
            if ($storeBubbleTea->getBubbleTea() === $this) {
                $storeBubbleTea->setBubbleTea(null);
            }
        }

        return $this;
    }

   
   
}
