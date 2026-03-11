<?php

namespace App\Repository;

use App\Entity\Patente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Patente>
 */
class PatenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Patente::class);
    }

    /**
     * Patenti in scadenza entro la data limite.
     *
     * @return Patente[]
     */
    public function findInScadenza(\DateTimeInterface $entro): array
    {
        $oggi = new \DateTime('today');

        return $this->createQueryBuilder('p')
            ->join('p.intestatario', 'ana')
            ->where('p.dataScadenzaPatente BETWEEN :oggi AND :entro')
            ->setParameter('oggi', $oggi)
            ->setParameter('entro', $entro)
            ->orderBy('p.dataScadenzaPatente', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
