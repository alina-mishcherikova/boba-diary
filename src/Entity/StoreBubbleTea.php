<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\StoreBubbleTeaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StoreBubbleTeaRepository::class)]
#[ApiResource]
class StoreBubbleTea
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'storeBubbleTeas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Store $store_id = null;

    #[ORM\ManyToOne(inversedBy: 'storeBubbleTeas')]
    #[ORM\JoinColumn(nullable: false)]
    private ?BubbleTea $bubbleTea = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $price = null;

    #[ORM\ManyToOne(inversedBy: 'storeBubbleTeas')]
    private ?Currency $currency = null;

    #[ORM\Column]
    private ?bool $is_available = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStoreId(): ?Store
    {
        return $this->store_id;
    }

    public function setStoreId(?Store $store_id): static
    {
        $this->store_id = $store_id;

        return $this;
    }

    public function getBubbleTea(): ?BubbleTea
    {
        return $this->bubbleTea;
    }

    public function setBubbleTea(?BubbleTea $bubbleTea): static
    {
        $this->bubbleTea = $bubbleTea;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(?Currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->is_available;
    }

    public function setIsAvailable(bool $is_available): static
    {
        $this->is_available = $is_available;

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
}
