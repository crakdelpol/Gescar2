<?php

namespace App\DataFixtures;

use App\DataFixtures\AnagraficaFixtures;
use App\Entity\Anagrafica;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Patente;
use DateTime;

class PatenteFixtures extends Fixture implements DependentFixtureInterface 
{
    public function load(ObjectManager $manager): void
    {
        $patente = new Patente();
        $patente->setCategoriaPatente(array('B'));
        $patente->setNumeroPatente('122332');
        $patente->setNote('Note patente  ');
        $patente->setDataScadenzaPatente(new DateTime('2025-07-22'));
        // this reference returns the Anagrafica object created in AnagraficaFixtures
        $patente->setIntestatario($this->getReference(AnagraficaFixtures::CLIENTE_TEST_1, Anagrafica::class));

        $manager->persist($patente);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AnagraficaFixtures::class,
        ];
    }
}