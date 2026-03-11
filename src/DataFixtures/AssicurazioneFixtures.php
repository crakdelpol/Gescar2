<?php

namespace App\DataFixtures;

use App\Entity\Assicurazione;
use App\Entity\Vettura;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AssicurazioneFixtures extends Fixture implements DependentFixtureInterface
{
    // vettura_ref,       scad_assicurazione,  compagnia,         numero_polizza
    private array $dati = [
        ['v-fiat-punto',    '-20 days',  'Generali',     'POL-2024-001'],   // SCADUTA
        ['v-vw-golf',       '+8 days',   'Unipol',       'POL-2024-002'],   // IN SCADENZA
        ['v-peugeot-308',   '+35 days',  'Allianz',      'POL-2024-003'],   // IN AVVICINAMENTO
        ['v-bmw-serie3',    '+180 days', 'AXA',          'POL-2024-004'],   // OK
        ['v-ford-focus',    '+12 days',  'Generali',     'POL-2024-005'],   // IN SCADENZA
        ['v-renault-clio',  '-10 days',  'Zurich',       'POL-2024-006'],   // SCADUTA
        ['v-toyota-yaris',  '+50 days',  'Unipol',       'POL-2024-007'],   // IN AVVICINAMENTO
        ['v-opel-corsa',    '+150 days', 'Allianz',      'POL-2024-008'],   // OK
        ['v-alfa-giulia',   '+20 days',  'Generali',     'POL-2024-009'],   // IN SCADENZA
        ['v-mercedes-a',    '-3 days',   'AXA',          'POL-2024-010'],   // SCADUTA
        ['v-fiat-ducato',   '+28 days',  'Unipol',       'POL-2024-011'],   // IN SCADENZA
        ['v-ford-transit',  '+90 days',  'Zurich',       'POL-2024-012'],   // OK
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->dati as $d) {
            $a = new Assicurazione();
            $a->setDataScadenzaAssicurazione(new \DateTime($d[1]));
            $a->setCompagnia($d[2]);
            $a->setNumeroPolizza($d[3]);
            $a->setDataInizio(new \DateTime('-1 year'));
            $a->setAttiva(true);
            $a->setVettura($this->getReference($d[0], Vettura::class));

            $manager->persist($a);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [VetturaFixtures::class];
    }
}
