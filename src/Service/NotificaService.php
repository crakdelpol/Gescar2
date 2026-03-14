<?php

namespace App\Service;

use App\Entity\Anagrafica;
use App\Entity\Notifica;
use App\Entity\User;
use App\Entity\Vettura;
use App\Repository\NotificaRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Gestisce la creazione e il tracciamento degli avvisi inviati ai clienti.
 */
class NotificaService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificaRepository     $notificaRepository,
    ) {}

    /**
     * Registra un nuovo avviso nel sistema.
     */
    public function registra(
        Anagrafica  $anagrafica,
        string      $tipoScadenza,
        string      $canale,
        string      $esito,
        ?string     $note = null,
        ?Vettura    $vettura = null,
        ?User       $utente = null,
    ): Notifica {
        $notifica = new Notifica();
        $notifica->setAnagrafica($anagrafica);
        $notifica->setTipoScadenza($tipoScadenza);
        $notifica->setCanale($canale);
        $notifica->setEsito($esito);
        $notifica->setNote($note);
        $notifica->setVettura($vettura);
        $notifica->setUtente($utente);

        $this->em->persist($notifica);
        $this->em->flush();

        return $notifica;
    }

    /**
     * Verifica se il cliente è già stato avvisato per questo tipo di scadenza
     * negli ultimi N giorni.
     */
    public function isGiaAvvisato(
        Anagrafica $anagrafica,
        string     $tipoScadenza,
        int        $giorni = 7
    ): bool {
        $da = new \DateTime("-{$giorni} days");

        $count = $this->notificaRepository->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.anagrafica = :anagrafica')
            ->andWhere('n.tipoScadenza = :tipo')
            ->andWhere('n.dataInvio >= :da')
            ->setParameter('anagrafica', $anagrafica)
            ->setParameter('tipo', $tipoScadenza)
            ->setParameter('da', $da)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Restituisce lo storico delle notifiche per un cliente.
     */
    public function getStoricoCliente(Anagrafica $anagrafica): array
    {
        return $this->notificaRepository->findByAnagrafica($anagrafica);
    }

    /**
     * Restituisce le notifiche recenti (ultimi 7 giorni per default).
     */
    public function getRecenti(int $giorni = 7): array
    {
        return $this->notificaRepository->findRecenti($giorni);
    }
}
