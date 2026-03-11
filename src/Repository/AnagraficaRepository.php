<?php

namespace App\Repository;

use App\Entity\Anagrafica;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Anagrafica>
 */
class AnagraficaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Anagrafica::class);
    }

    /**
     * Ricerca full-text per cognome, nome o ragione sociale.
     * Usata dalla barra di ricerca globale.
     *
     * @return Anagrafica[]
     */
    public function search(string $q): array
    {
        $q = '%' . trim($q) . '%';

        return $this->createQueryBuilder('a')
            ->where('a.cognome LIKE :q OR a.nome LIKE :q')
            ->setParameter('q', $q)
            ->orderBy('a.cognome', 'ASC')
            ->addOrderBy('a.nome', 'ASC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }

    /**
     * Ricerca per numero di telefono (parziale).
     *
     * @return Anagrafica[]
     */
    public function findByTelefono(string $telefono): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.telefono LIKE :t')
            ->setParameter('t', '%' . trim($telefono) . '%')
            ->orderBy('a.cognome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Ricerca universale: cognome/nome OPPURE targa veicolo.
     * Utile per la ricerca rapida da dashboard.
     *
     * @return Anagrafica[]
     */
    public function searchGlobale(string $q): array
    {
        $like = '%' . trim($q) . '%';

        return $this->createQueryBuilder('a')
            ->leftJoin('App\Entity\Vettura', 'v', 'WITH', 'v.intestatario = a')
            ->where('a.cognome LIKE :q OR a.nome LIKE :q OR v.targa LIKE :targa')
            ->setParameter('q', $like)
            ->setParameter('targa', '%' . strtoupper(trim($q)) . '%')
            ->orderBy('a.cognome', 'ASC')
            ->distinct()
            ->setMaxResults(30)
            ->getQuery()
            ->getResult();
    }
}
