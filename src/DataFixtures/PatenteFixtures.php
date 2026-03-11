<?php

namespace App\DataFixtures;

use App\Entity\Anagrafica;
use App\Entity\Patente;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PatenteFixtures extends Fixture implements DependentFixtureInterface
{
    // cliente_ref,        numero,      categorie,         scadenza
    private array $dati = [
        ['rossi-mario',      'SV001234',  ['B'],            '-10 days'],   // SCADUTA
        ['bianchi-lucia',    'SV002345',  ['B'],            '+20 days'],   // IN SCADENZA
        ['ferrari-giuseppe', 'SV003456',  ['B', 'BE'],      '+50 days'],   // IN AVVICINAMENTO
        ['esposito-anna',    'SV004567',  ['B'],            '+180 days'],  // OK
        ['conti-roberto',    'SV005678',  ['B', 'C', 'CE'], '+8 days'],    // IN SCADENZA
        ['gallo-francesca',  'SV006789',  ['B'],            '+90 days'],   // OK
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->dati as $d) {
            $p = new Patente();
            $p->setNumeroPatente($d[1]);
            $p->setCategoriaPatente($d[2]);
            $p->setDataScadenzaPatente(new \DateTime($d[3]));
            $p->setIntestatario($this->getReference($d[0], Anagrafica::class));

            $manager->persist($p);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [AnagraficaFixtures::class];
    }
}
