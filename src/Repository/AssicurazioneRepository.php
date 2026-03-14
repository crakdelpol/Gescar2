<?php

namespace App\Repository;

use App\Entity\Assicurazione;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Assicurazione>
 */
class AssicurazioneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Assicurazione::class);
    }

    /**
     * Assicurazioni in scadenza entro la data limite.
     * Restituisce solo le polizze attive (attiva = 1), se il campo esiste.
     *
     * @return Assicurazione[]
     */
    public function findInScadenza(\DateTimeInterface $entro): array
    {
        $oggi = new \DateTime('today');

        return $this->createQueryBuilder('a')
            ->join('a.vettura', 'v')
            ->join('v.intestatario', 'ana')
            ->where('a.dataScadenzaAssicurazione BETWEEN :oggi AND :entro')
            ->setParameter('oggi', $oggi)
            ->setParameter('entro', $entro)
            ->orderBy('a.dataScadenzaAssicurazione', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Assicurazioni con scadenza in un intervallo di date arbitrario.
     * Usata dalla pagina Scadenze per la ricerca per periodo.
     *
     * @return Assicurazione[]
     */
    public function findInRange(\DateTimeInterface $da, \DateTimeInterface $a): array
    {
        return $this->createQueryBuilder('ass')
            ->join('ass.vettura', 'v')
            ->join('v.intestatario', 'ana')
            ->where('ass.dataScadenzaAssicurazione BETWEEN :da AND :a')
            ->setParameter('da', $da)
            ->setParameter('a', $a)
            ->orderBy('ass.dataScadenzaAssicurazione', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
