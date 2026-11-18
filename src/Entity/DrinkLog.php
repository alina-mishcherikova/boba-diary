<?php

namespace App\Entity;

use App\Repository\DrinkLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Temperature;
use App\Enum\IceLevel;
use App\Enum\SugarLevel;

#[ORM\Entity(repositoryClass: DrinkLogRepository::class)]
class DrinkLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $user_id = null;

    #[ORM\Column]
    private ?int $store_bubble_tea_id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $consumed_at = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 2, scale: 1)]
    private ?string $rating = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo_url = null;


    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $paid_price = null;

    #[ORM\Column(nullable: true)]
    private ?int $paid_currency_id = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, enumType: Temperature::class)]
    private ?int $temperature = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, enumType: SugarLevel::class)]
    private ?int $sugar_level = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true, enumType: IceLevel::class)]
    private ?int $ice_level = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getStoreBubbleTeaId(): ?int
    {
        return $this->store_bubble_tea_id;
    }

    public function setStoreBubbleTeaId(int $store_bubble_tea_id): static
    {
        $this->store_bubble_tea_id = $store_bubble_tea_id;

        return $this;
    }

    public function getConsumedAt(): ?\DateTimeImmutable
    {
        return $this->consumed_at;
    }

    public function setConsumedAt(\DateTimeImmutable $consumed_at): static
    {
        $this->consumed_at = $consumed_at;

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

    public function getRating(): ?string
    {
        return $this->rating;
    }

    public function setRating(string $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): static
    {
        $this->note = $note;

        return $this;
    }

    public function getPhotoUrl(): ?string
    {
        return $this->photo_url;
    }

    public function setPhotoUrl(?string $photo_url): static
    {
        $this->photo_url = $photo_url;

        return $this;
    }


    public function getPaidPrice(): ?string
    {
        return $this->paid_price;
    }

    public function setPaidPrice(?string $paid_price): static
    {
        $this->paid_price = $paid_price;

        return $this;
    }

    public function getPaidCurrencyId(): ?int
    {
        return $this->paid_currency_id;
    }

    public function setPaidCurrencyId(?int $paid_currency_id): static
    {
        $this->paid_currency_id = $paid_currency_id;

        return $this;
    }

    public function getTemperature(): ?Temperature
    {
        return $this->temperature;
    }

    public function setTemperature(?Temperature $temperature): static
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getSugarLevel(): ?SugarLevel
    {
        return $this->sugar_level;
    }

    public function setSugarLevel(?SugarLevel $sugar_level): static
    {
        $this->sugar_level = $sugar_level;

        return $this;
    }

    public function getIceLevel(): ?IceLevel
    {
        return $this->ice_level;
    }

    public function setIceLevel(?IceLevel $ice_level): static
    {
        $this->ice_level = $ice_level;

        return $this;
    }
}
