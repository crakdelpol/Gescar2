<?php

namespace App\Entity;

use App\Repository\NotificaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificaRepository::class)]
#[ORM\Table(name: 'notifica')]
#[ORM\Index(fields: ['dataInvio'], name: 'idx_notifica_data_invio')]
#[ORM\Index(fields: ['tipoScadenza'], name: 'idx_notifica_tipo')]
class Notifica
{
    public const TIPO_REVISIONE    = 'revisione';
    public const TIPO_ASSICURAZIONE = 'assicurazione';
    public const TIPO_BOLLO        = 'bollo';
    public const TIPO_PATENTE      = 'patente';

    public const CANALE_TELEFONO  = 'telefono';
    public const CANALE_SMS       = 'sms';
    public const CANALE_EMAIL     = 'email';
    public const CANALE_WHATSAPP  = 'whatsapp';

    public const ESITO_INVIATA          = 'inviata';
    public const ESITO_NON_RISPONDE     = 'non_risponde';
    public const ESITO_RINNOVATO        = 'rinnovato';
    public const ESITO_NON_INTERESSATO  = 'non_interessato';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Anagrafica::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Anagrafica $anagrafica = null;

    #[ORM\ManyToOne(targetEntity: Vettura::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Vettura $vettura = null;

    #[ORM\Column(length: 30)]
    private ?string $tipoScadenza = null;

    #[ORM\Column(length: 20)]
    private ?string $canale = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dataInvio = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $esito = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $note = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $utente = null;

    public function __construct()
    {
        $this->dataInvio = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAnagrafica(): ?Anagrafica
    {
        return $this->anagrafica;
    }

    public function setAnagrafica(?Anagrafica $anagrafica): static
    {
        $this->anagrafica = $anagrafica;

        return $this;
    }

    public function getVettura(): ?Vettura
    {
        return $this->vettura;
    }

    public function setVettura(?Vettura $vettura): static
    {
        $this->vettura = $vettura;

        return $this;
    }

    public function getTipoScadenza(): ?string
    {
        return $this->tipoScadenza;
    }

    public function setTipoScadenza(string $tipoScadenza): static
    {
        $this->tipoScadenza = $tipoScadenza;

        return $this;
    }

    public function getCanale(): ?string
    {
        return $this->canale;
    }

    public function setCanale(string $canale): static
    {
        $this->canale = $canale;

        return $this;
    }

    public function getDataInvio(): ?\DateTimeInterface
    {
        return $this->dataInvio;
    }

    public function setDataInvio(\DateTimeInterface $dataInvio): static
    {
        $this->dataInvio = $dataInvio;

        return $this;
    }

    public function getEsito(): ?string
    {
        return $this->esito;
    }

    public function setEsito(?string $esito): static
    {
        $this->esito = $esito;

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

    public function getUtente(): ?User
    {
        return $this->utente;
    }

    public function setUtente(?User $utente): static
    {
        $this->utente = $utente;

        return $this;
    }

    // Helper: array di tipi scadenza per ChoiceType
    public static function getTipiScadenza(): array
    {
        return [
            'Revisione'     => self::TIPO_REVISIONE,
            'Assicurazione' => self::TIPO_ASSICURAZIONE,
            'Bollo'         => self::TIPO_BOLLO,
            'Patente'       => self::TIPO_PATENTE,
        ];
    }

    public static function getCanali(): array
    {
        return [
            'Telefono'  => self::CANALE_TELEFONO,
            'SMS'       => self::CANALE_SMS,
            'Email'     => self::CANALE_EMAIL,
            'WhatsApp'  => self::CANALE_WHATSAPP,
        ];
    }

    public static function getEsiti(): array
    {
        return [
            'Inviata'           => self::ESITO_INVIATA,
            'Non risponde'      => self::ESITO_NON_RISPONDE,
            'Rinnovato'         => self::ESITO_RINNOVATO,
            'Non interessato'   => self::ESITO_NON_INTERESSATO,
        ];
    }
}
