<?php

namespace App\Repository;

use App\Entity\Anagrafica;
use App\Entity\Notifica;
use App\Entity\Vettura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notifica>
 */
class NotificaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notifica::class);
    }

    /**
     * Restituisce le notifiche per un'anagrafica, ordinate per data decrescente.
     */
    public function findByAnagrafica(Anagrafica $anagrafica): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.anagrafica = :anagrafica')
            ->setParameter('anagrafica', $anagrafica)
            ->orderBy('n.dataInvio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Restituisce le notifiche per un veicolo.
     */
    public function findByVettura(Vettura $vettura): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.vettura = :vettura')
            ->setParameter('vettura', $vettura)
            ->orderBy('n.dataInvio', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Restituisce le notifiche degli ultimi N giorni.
     */
    public function findRecenti(int $giorni = 7): array
    {
        $da = new \DateTime("-{$giorni} days");

        return $this->createQueryBuilder('n')
            ->andWhere('n.dataInvio >= :da')
            ->setParameter('da', $da)
            ->orderBy('n.dataInvio', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
