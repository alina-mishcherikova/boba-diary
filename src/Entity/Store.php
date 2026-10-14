<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\StoreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoreRepository::class)]
#[ApiResource]
class Store
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\Column(length: 255)]
    private ?string $city = null;

    #[ORM\Column(length: 100)]
    private ?string $country = null;

    #[ORM\Column(length: 20)]
    private ?string $postal_code = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image_url = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $inactivated_at = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $created_by = null;

    /**
     * @var Collection<int, StoreBubbleTea>
     */
    #[ORM\OneToMany(targetEntity: StoreBubbleTea::class, mappedBy: 'store_id')]
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

    public function getaddress(): ?string
    {
        return $this->address;
    }

    public function setaddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(string $postal_code): static
    {
        $this->postal_code = $postal_code;

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

    public function getInactivatedAt(): ?\DateTimeImmutable
    {
        return $this->inactivated_at;
    }

    public function setInactivatedAt(?\DateTimeImmutable $inactivated_at): static
    {
        $this->inactivated_at = $inactivated_at;

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
            $storeBubbleTea->setStoreId($this);
        }

        return $this;
    }

    public function removeStoreBubbleTea(StoreBubbleTea $storeBubbleTea): static
    {
        if ($this->storeBubbleTeas->removeElement($storeBubbleTea)) {
            // set the owning side to null (unless already changed)
            if ($storeBubbleTea->getStoreId() === $this) {
                $storeBubbleTea->setStoreId(null);
            }
        }

        return $this;
    }
}
