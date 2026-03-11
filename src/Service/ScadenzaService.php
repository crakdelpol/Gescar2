<?php

namespace App\Service;

use App\Repository\AssicurazioneRepository;
use App\Repository\BolloRepository;
use App\Repository\PatenteRepository;
use App\Repository\VetturaRepository;

/**
 * Calcola stati e scadenze imminenti per la dashboard.
 *
 * Soglie di allerta (da 03_BUSINESS_RULES.md):
 *  - Scaduto        : data_scadenza < OGGI
 *  - In scadenza    : entro 30 giorni  → badge danger
 *  - In avvicinamento: entro 60 giorni → badge warning
 *  - Ok             : oltre 60 giorni  → badge success
 *  - Non definito   : NULL             → badge secondary
 */
class ScadenzaService
{
    // Colori Bootstrap corrispondenti agli stati
    public const STATO_SCADUTO       = 'scaduto';
    public const STATO_IN_SCADENZA   = 'in_scadenza';
    public const STATO_IN_AVVICINAMENTO = 'in_avvicinamento';
    public const STATO_OK            = 'ok';
    public const STATO_NON_DEFINITO  = 'non_definito';

    public const BADGE_MAP = [
        self::STATO_SCADUTO          => 'danger',
        self::STATO_IN_SCADENZA      => 'warning',
        self::STATO_IN_AVVICINAMENTO => 'info',
        self::STATO_OK               => 'success',
        self::STATO_NON_DEFINITO     => 'secondary',
    ];

    public function __construct(
        private readonly VetturaRepository       $vetturaRepository,
        private readonly AssicurazioneRepository $assicurazioneRepository,
        private readonly BolloRepository         $bolloRepository,
        private readonly PatenteRepository       $patenteRepository,
    ) {}

    /**
     * Calcola lo stato di una scadenza rispetto a oggi.
     */
    public function calcolaStato(?\DateTimeInterface $dataScadenza): string
    {
        if ($dataScadenza === null) {
            return self::STATO_NON_DEFINITO;
        }

        $oggi   = new \DateTime('today');
        $diff   = (int) $oggi->diff($dataScadenza)->format('%r%a'); // positivo = futuro

        if ($diff < 0) {
            return self::STATO_SCADUTO;
        }
        if ($diff <= 30) {
            return self::STATO_IN_SCADENZA;
        }
        if ($diff <= 60) {
            return self::STATO_IN_AVVICINAMENTO;
        }

        return self::STATO_OK;
    }

    /**
     * Restituisce il badge Bootstrap per uno stato.
     */
    public function getBadge(string $stato): string
    {
        return self::BADGE_MAP[$stato] ?? 'secondary';
    }

    /**
     * Dati aggregati per la dashboard principale.
     *
     * @return array{
     *   revisioni_scadute: array,
     *   revisioni_30gg: array,
     *   assicurazioni_30gg: array,
     *   patenti_60gg: array,
     *   bolli_mese_corrente: array
     * }
     */
    public function getSommarioDashboard(): array
    {
        $oggi  = new \DateTime('today');
        $fine30 = (new \DateTime('today'))->modify('+30 days');
        $fine60 = (new \DateTime('today'))->modify('+60 days');

        $inizioMese = new \DateTime('first day of this month');
        $fineMese   = new \DateTime('last day of this month');

        return [
            'revisioni_scadute'   => $this->vetturaRepository->findRevisioniInRange(
                new \DateTime('1970-01-01'), $oggi
            ),
            'revisioni_30gg'      => $this->vetturaRepository->findRevisioniInRange(
                $oggi, $fine30
            ),
            'assicurazioni_30gg'  => $this->assicurazioneRepository->findInScadenza($fine30),
            'patenti_60gg'        => $this->patenteRepository->findInScadenza($fine60),
            'bolli_mese_corrente' => $this->bolloRepository->findInRange($inizioMese, $fineMese),
        ];
    }
}
