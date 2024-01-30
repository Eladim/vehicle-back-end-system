<?php

namespace App\Entity;

use App\Repository\TrailerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TrailerRepository::class)]
class Trailer extends Product
{
    #[ORM\Column]
    #[Assert\NotBlank(message: "Load Capacity cannot be empty")]
    private ?int $loadCapacity = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Number Of Axles cannot be empty")]
    private ?int $numberOfAxles = null;

    public function getLoadCapacity(): ?int
    {
        return $this->loadCapacity;
    }

    public function setLoadCapacity(int $loadCapacity): static
    {
        $this->loadCapacity = $loadCapacity;

        return $this;
    }

    public function getNumberOfAxles(): ?int
    {
        return $this->numberOfAxles;
    }

    public function setNumberOfAxles(int $numberOfAxles): static
    {
        $this->numberOfAxles = $numberOfAxles;

        return $this;
    }
}
