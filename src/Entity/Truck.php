<?php

namespace App\Entity;

use App\Repository\TruckRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TruckRepository::class)]
class Truck extends Product
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Engine Capacity cannot be empty")]
    private ?string $engineCapacity = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Colour cannot be empty")]
    private ?string $colour = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Number of Beds cannot be empty")]
    private ?int $numberOfBeds = null;

    public function getEngineCapacity(): ?string
    {
        return $this->engineCapacity;
    }

    public function setEngineCapacity(string $engineCapacity): static
    {
        $this->engineCapacity = $engineCapacity;

        return $this;
    }

    public function getColour(): ?string
    {
        return $this->colour;
    }

    public function setColour(string $colour): static
    {
        $this->colour = $colour;

        return $this;
    }

    public function getNumberOfBeds(): ?int
    {
        return $this->numberOfBeds;
    }

    public function setNumberOfBeds(int $numberOfBeds): static
    {
        $this->numberOfBeds = $numberOfBeds;

        return $this;
    }
}
