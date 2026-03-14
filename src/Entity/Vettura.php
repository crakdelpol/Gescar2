<?php

namespace App\Entity;

use App\Repository\VetturaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VetturaRepository::class)]
class Vettura
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $targa = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $numeroTelaio = null;

    // se autovettura, motociclo, autocarro
    #[ORM\Column(name:"tipo_vettura", length: 20, nullable: true)]
    private ?string $tipo = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $marca = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $modello = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dataUltimaRevisione = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTime $dataScadenzaRevisione = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Anagrafica $intestatario = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $carburante = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dataScadenzaImpianto = null;

    /** Veicoli esenti da revisione (es. veicoli storici, rimorchi, ecc.) */
    #[ORM\Column(options: ['default' => 0])]
    private bool $esenteRevisione = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTarga(): ?string
    {
        return $this->targa;
    }

    public function setTarga(?string $targa): static
    {
        $this->targa = $targa;

        return $this;
    }

    public function getNumeroTelaio(): ?string
    {
        return $this->numeroTelaio;
    }

    public function setNumeroTelaio(?string $numeroTelaio): static
    {
        $this->numeroTelaio = $numeroTelaio;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(?string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getMarca(): ?string
    {
        return $this->marca;
    }

    public function setMarca(?string $marca): static
    {
        $this->marca = $marca;

        return $this;
    }

    public function getModello(): ?string
    {
        return $this->modello;
    }

    public function setModello(?string $modello): static
    {
        $this->modello = $modello;

        return $this;
    }

    public function getDataUltimaRevisione(): ?\DateTime
    {
        return $this->dataUltimaRevisione;
    }

    public function setDataUltimaRevisione(?\DateTime $dataUltimaRevisione): static
    {
        $this->dataUltimaRevisione = $dataUltimaRevisione;

        return $this;
    }

    public function getDataScadenzaRevisione(): ?\DateTime
    {
        return $this->dataScadenzaRevisione;
    }

    public function setDataScadenzaRevisione(?\DateTime $dataScadenzaRevisione): static
    {
        $this->dataScadenzaRevisione = $dataScadenzaRevisione;

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

    public function getIntestatario(): ?Anagrafica
    {
        return $this->intestatario;
    }

    public function setIntestatario(?Anagrafica $intestatario): static
    {
        $this->intestatario = $intestatario;

        return $this;
    }

    public function getCarburante(): ?string
    {
        return $this->carburante;
    }

    public function setCarburante(?string $carburante): static
    {
        $this->carburante = $carburante;

        return $this;
    }

    public function getDataScadenzaImpianto(): ?\DateTime
    {
        return $this->dataScadenzaImpianto;
    }

    public function setDataScadenzaImpianto(?\DateTime $dataScadenzaImpianto): static
    {
        $this->dataScadenzaImpianto = $dataScadenzaImpianto;

        return $this;
    }

    public function isEsenteRevisione(): bool
    {
        return $this->esenteRevisione;
    }

    public function setEsenteRevisione(bool $esenteRevisione): static
    {
        $this->esenteRevisione = $esenteRevisione;

        return $this;
    }
}
