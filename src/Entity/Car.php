<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CarRepository::class)]
class Car extends Product
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Engine Capacity cannot be empty")]
    private ?string $engineCapacity = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Colour cannot be empty")]
    private ?string $colour = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Number of Doors cannot be empty")]
    private ?int $numberOfDoors = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Category cannot be empty")]
    private ?string $category = null;

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

    public function getNumberOfDoors(): ?int
    {
        return $this->numberOfDoors;
    }

    public function setNumberOfDoors(int $numberOfDoors): static
    {
        $this->numberOfDoors = $numberOfDoors;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }
}
