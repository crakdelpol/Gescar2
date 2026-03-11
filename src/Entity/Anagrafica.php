<?php

namespace App\Entity;

use App\Repository\AnagraficaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnagraficaRepository::class)]
class Anagrafica
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: "id")]
    private ?int $id = null;

    #[ORM\Column(name: "nome", length: 100, nullable: true)]
    private ?string $nome = null;

    #[ORM\Column(name: "cognome", length: 100, nullable: true)]
    private ?string $cognome = null;

    #[ORM\Column(name: "tipo_cliente", length: 20, nullable: true)]
    private ?string $tipo = null;

    #[ORM\Column(name: "luogo_nascita", length: 50, nullable: true)]
    private ?string $luogoNascita = null;

    #[ORM\Column(name: "codice_fiscale", length: 16, nullable: true)]
    private ?string $codiceFiscale = null;

    #[ORM\Column(name: "partita_iva", length: 11, nullable: true)]
    private ?string $partitaIva = null;

    #[ORM\Column(name: "residenza", length: 255, nullable: true)]
    private ?string $residenza = null;

    #[ORM\Column(name: "sede_legale", length: 255, nullable: true)]
    private ?string $sedeLegale = null;

    #[ORM\Column(name: "email", length: 100, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(name: "telefono", length: 100, nullable: true)]
    private ?string $telefono = null;

    // fatturazione elettronica
    #[ORM\Column(name: "codice_destinatario", length: 255, nullable: true)]
    private ?string $codiceDestinatario = null;

    #[ORM\Column(name: "note", length: 255, nullable: true)]
    private ?string $note = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(?string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getCognome(): ?string
    {
        return $this->cognome;
    }

    public function setCognome(?string $cognome): static
    {
        $this->cognome = $cognome;

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
    public function getLuogoNascita(): ?string
    {
        return $this->luogoNascita;
    }

    public function setLuogoNascita(?string $luogoNascita): static
    {
        $this->luogoNascita = $luogoNascita;

        return $this;
    }

    public function getCodiceFiscale(): ?string
    {
        return $this->codiceFiscale;
    }

    public function setCodiceFiscale(?string $codiceFiscale): static
    {
        $this->codiceFiscale = $codiceFiscale;

        return $this;
    }

    public function getPartitaIva(): ?string
    {
        return $this->partitaIva;
    }

    public function setPartitaIva(?string $partitaIva): static
    {
        $this->partitaIva = $partitaIva;

        return $this;
    }

    public function getResidenza(): ?string
    {
        return $this->residenza;
    }

    public function setResidenza(?string $residenza): static
    {
        $this->residenza = $residenza;

        return $this;
    }

    public function getSedeLegale(): ?string
    {
        return $this->sedeLegale;
    }

    public function setSedeLegale(?string $sedeLegale): static
    {
        $this->sedeLegale = $sedeLegale;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function setTelefono(?string $telefono): static
    {
        $this->telefono = $telefono;

        return $this;
    }

    public function getCodiceDestinatario(): ?string
    {
        return $this->codiceDestinatario;
    }

    public function setCodiceDestinatario(?string $codiceDestinatario): static
    {
        $this->codiceDestinatario = $codiceDestinatario;

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
