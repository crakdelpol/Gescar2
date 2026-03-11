<?php

namespace App\Entity;

use App\Repository\AssicurazioneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AssicurazioneRepository::class)]
class Assicurazione
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vettura $vettura = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dataScadenzaAssicurazione = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDataScadenzaAssicurazione(): ?\DateTime
    {
        return $this->dataScadenzaAssicurazione;
    }

    public function setDataScadenzaAssicurazione(\DateTime $dataScadenzaAssicurazione): static
    {
        $this->dataScadenzaAssicurazione = $dataScadenzaAssicurazione;

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
