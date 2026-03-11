<?php

namespace App\Entity;

use App\Repository\PatenteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PatenteRepository::class)]
class Patente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $numeroPatente = null;

    #[ORM\Column(nullable: true)]
    private ?array $categoriaPatente = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dataScadenzaPatente = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Anagrafica $intestatario = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumeroPatente(): ?string
    {
        return $this->numeroPatente;
    }

    public function setNumeroPatente(?string $numeroPatente): static
    {
        $this->numeroPatente = $numeroPatente;

        return $this;
    }

    public function getCategoriaPatente(): ?array
    {
        return $this->categoriaPatente;
    }

    public function setCategoriaPatente(?array $categoriaPatente): static
    {
        $this->categoriaPatente = $categoriaPatente;

        return $this;
    }

    public function getDataScadenzaPatente(): ?\DateTime
    {
        return $this->dataScadenzaPatente;
    }

    public function setDataScadenzaPatente(?\DateTime $dataScadenzaPatente): static
    {
        $this->dataScadenzaPatente = $dataScadenzaPatente;

        return $this;
    }

    public function getIntestatario(): ?Anagrafica
    {
        return $this->intestatario;
    }

    public function setIntestatario(Anagrafica $intestatario): static
    {
        $this->intestatario = $intestatario;

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
}
