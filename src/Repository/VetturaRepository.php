<?php

namespace App\Repository;

use App\Entity\Vettura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Vettura>
 */
class VetturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Vettura::class);
    }

    /**
     * Veicoli con scadenza revisione in un intervallo di date.
     * Esclude veicoli esenti dalla revisione.
     *
     * @return Vettura[]
     */
    public function findRevisioniInRange(\DateTimeInterface $da, \DateTimeInterface $a): array
    {
        return $this->createQueryBuilder('v')
            ->join('v.intestatario', 'ana')
            ->where('v.dataScadenzaRevisione BETWEEN :da AND :a')
            ->andWhere('v.dataScadenzaRevisione IS NOT NULL')
            ->setParameter('da', $da)
            ->setParameter('a', $a)
            ->orderBy('v.dataScadenzaRevisione', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Ricerca per targa (case-insensitive, parziale).
     *
     * @return Vettura[]
     */
    public function findByTargaLike(string $targa): array
    {
        return $this->createQueryBuilder('v')
            ->where('v.targa LIKE :targa')
            ->setParameter('targa', '%' . strtoupper(trim($targa)) . '%')
            ->orderBy('v.targa', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
