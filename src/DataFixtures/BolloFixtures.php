<?php

namespace App\DataFixtures;

use App\Entity\Bollo;
use App\Entity\Vettura;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BolloFixtures extends Fixture implements DependentFixtureInterface
{
    // vettura_ref,       scad_bollo,    importo, pagato
    private array $dati = [
        ['v-fiat-punto',    '-5 days',   145.00, false],   // SCADUTO
        ['v-vw-golf',       '+15 days',  234.00, false],   // IN SCADENZA
        ['v-peugeot-308',   '+45 days',  189.00, false],   // IN AVVICINAMENTO
        ['v-bmw-serie3',    '+200 days', 320.00, true],    // OK (pagato)
        ['v-ford-focus',    '+7 days',   178.00, false],   // IN SCADENZA
        ['v-renault-clio',  '-25 days',  156.00, false],   // SCADUTO
        ['v-toyota-yaris',  '+58 days',  167.00, false],   // IN AVVICINAMENTO
        ['v-opel-corsa',    '+160 days', 145.00, true],    // OK (pagato)
        ['v-alfa-giulia',   '+22 days',  267.00, false],   // IN SCADENZA
        ['v-mercedes-a',    '-8 days',   412.00, false],   // SCADUTO
        ['v-fiat-ducato',   '+18 days',  534.00, false],   // IN SCADENZA
        ['v-ford-transit',  '+95 days',  612.00, true],    // OK (pagato)
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->dati as $d) {
            $b = new Bollo();
            $b->setDataScadenzaBollo(new \DateTime($d[1]));
            $b->setImporto($d[2]);
            $b->setPagato($d[3]);
            $b->setAttiva(true);
            $b->setVettura($this->getReference($d[0], Vettura::class));

            $manager->persist($b);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [VetturaFixtures::class];
    }
}
