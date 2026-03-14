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

    // OneToOne: in Italia una vettura ha una sola assicurazione RC attiva
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vettura $vettura = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dataScadenzaAssicurazione = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dataInizio = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $compagnia = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroPolizza = null;

    #[ORM\Column(options: ['default' => 1])]
    private bool $attiva = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTime $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    // ── Getter / Setter ────────────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getVettura(): ?Vettura { return $this->vettura; }
    public function setVettura(Vettura $vettura): static { $this->vettura = $vettura; return $this; }

    public function getDataScadenzaAssicurazione(): ?\DateTime { return $this->dataScadenzaAssicurazione; }
    public function setDataScadenzaAssicurazione(\DateTime $d): static { $this->dataScadenzaAssicurazione = $d; return $this; }

    public function getDataInizio(): ?\DateTime { return $this->dataInizio; }
    public function setDataInizio(?\DateTime $d): static { $this->dataInizio = $d; return $this; }

    public function getCompagnia(): ?string { return $this->compagnia; }
    public function setCompagnia(?string $c): static { $this->compagnia = $c; return $this; }

    public function getNumeroPolizza(): ?string { return $this->numeroPolizza; }
    public function setNumeroPolizza(?string $n): static { $this->numeroPolizza = $n; return $this; }

    public function isAttiva(): bool { return $this->attiva; }
    public function setAttiva(bool $a): static { $this->attiva = $a; return $this; }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): static { $this->note = $note; return $this; }

    public function getCreatedAt(): ?\DateTime { return $this->createdAt; }
    public function setCreatedAt(\DateTime $createdAt): static { $this->createdAt = $createdAt; return $this; }
}
