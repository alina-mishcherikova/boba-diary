<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
#[ApiResource]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3)]
    private ?string $code = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 10)]
    private ?string $symbol = null;

    #[ORM\Column]
    private ?bool $is_active = null;

    /**
     * @var Collection<int, StoreBubbleTea>
     */
    #[ORM\OneToMany(targetEntity: StoreBubbleTea::class, mappedBy: 'currency')]
    private Collection $storeBubbleTeas;

    public function __construct()
    {
        $this->storeBubbleTeas = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
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

    public function getSymbol(): ?string
    {
        return $this->symbol;
    }

    public function setSymbol(string $symbol): static
    {
        $this->symbol = $symbol;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->is_active;
    }

    public function setIsActive(bool $is_active): static
    {
        $this->is_active = $is_active;

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
            $storeBubbleTea->setCurrency($this);
        }

        return $this;
    }

    public function removeStoreBubbleTea(StoreBubbleTea $storeBubbleTea): static
    {
        if ($this->storeBubbleTeas->removeElement($storeBubbleTea)) {
            // set the owning side to null (unless already changed)
            if ($storeBubbleTea->getCurrency() === $this) {
                $storeBubbleTea->setCurrency(null);
            }
        }

        return $this;
    }
}
