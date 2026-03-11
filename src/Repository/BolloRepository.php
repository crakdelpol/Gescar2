<?php

namespace App\Repository;

use App\Entity\Bollo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Bollo>
 */
class BolloRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Bollo::class);
    }

    /**
     * Bolli con scadenza in un intervallo di date.
     *
     * @return Bollo[]
     */
    public function findInRange(\DateTimeInterface $da, \DateTimeInterface $a): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.vettura', 'v')
            ->join('v.intestatario', 'ana')
            ->where('b.dataScadenzaBollo BETWEEN :da AND :a')
            ->andWhere('b.dataScadenzaBollo IS NOT NULL')
            ->setParameter('da', $da)
            ->setParameter('a', $a)
            ->orderBy('b.dataScadenzaBollo', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
