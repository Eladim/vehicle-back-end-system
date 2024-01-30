<?php

namespace App\Entity;

use App\Repository\MotorcycleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: MotorcycleRepository::class)]
class Motorcycle extends Product
{

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Engine capacity cannot be empty")]
    private ?string $engineCapacity = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Colour cannot be empty")]
    private ?string $colour = null;


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
}
