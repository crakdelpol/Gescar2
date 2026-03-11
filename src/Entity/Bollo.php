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

    // OneToOne: in Italia una vettura ha un solo bollo annuale
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vettura $vettura = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dataScadenzaBollo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $importo = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 8, scale: 2, nullable: true)]
    private ?string $superBollo = null;

    #[ORM\Column(options: ['default' => 0])]
    private bool $pagato = false;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dataPagamento = null;

    #[ORM\Column(options: ['default' => 1])]
    private bool $attiva = true;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    // ── Getter / Setter ────────────────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }

    public function getVettura(): ?Vettura { return $this->vettura; }
    public function setVettura(Vettura $vettura): static { $this->vettura = $vettura; return $this; }

    public function getDataScadenzaBollo(): ?\DateTime { return $this->dataScadenzaBollo; }
    public function setDataScadenzaBollo(?\DateTime $d): static { $this->dataScadenzaBollo = $d; return $this; }

    public function getImporto(): ?string { return $this->importo; }
    public function setImporto(?float $i): static { $this->importo = $i !== null ? (string)$i : null; return $this; }

    public function getSuperBollo(): ?string { return $this->superBollo; }
    public function setSuperBollo(?float $s): static { $this->superBollo = $s !== null ? (string)$s : null; return $this; }

    public function isPagato(): bool { return $this->pagato; }
    public function setPagato(bool $p): static { $this->pagato = $p; return $this; }

    public function getDataPagamento(): ?\DateTime { return $this->dataPagamento; }
    public function setDataPagamento(?\DateTime $d): static { $this->dataPagamento = $d; return $this; }

    public function isAttiva(): bool { return $this->attiva; }
    public function setAttiva(bool $a): static { $this->attiva = $a; return $this; }

    public function getNote(): ?string { return $this->note; }
    public function setNote(?string $note): static { $this->note = $note; return $this; }
}
