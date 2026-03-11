<?php

namespace App\Entity;

use App\Repository\BolloRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BolloRepository::class)]
class Bollo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dataScadenzaBollo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vettura $vettura = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDataScadenzaBollo(): ?\DateTime
    {
        return $this->dataScadenzaBollo;
    }

    public function setDataScadenzaBollo(?\DateTime $dataScadenzaBollo): static
    {
        $this->dataScadenzaBollo = $dataScadenzaBollo;

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

    public function getVettura(): ?Vettura
    {
        return $this->vettura;
    }

    public function setVettura(Vettura $vettura): static
    {
        $this->vettura = $vettura;

        return $this;
    }
}
