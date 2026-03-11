<?php

namespace App\DataFixtures;

use App\DataFixtures\AnagraficaFixtures;
use App\Entity\Anagrafica;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Vettura;
use DateTime;

class VetturaFixtures extends Fixture implements DependentFixtureInterface 
{
    public const VETTURA_TEST_1 = 'vettura-test-1';

    public function load(ObjectManager $manager): void
    {
        $vettura = new Vettura();
        $vettura->setMarca('Fiat');
        $vettura->setModello('Punto');
        $vettura->setNumeroTelaio('1212000');
        $vettura->setTipo('Autovettura');
        $vettura->setTarga('SV123D');
        $vettura->setNote('NOTE   ');
        $vettura->setDataUltimaRevisione(new DateTime('2023-07-22'));
        $vettura->setDataScadenzaRevisione(new DateTime('2025-07-22'));
        // this reference returns the Anagrafica object created in AnagraficaFixtures
        $vettura->setIntestatario($this->getReference(AnagraficaFixtures::CLIENTE_TEST_1, Anagrafica::class));

        $manager->persist($vettura);
        $manager->flush();

        // other fixtures can get this object using the AnagraficaFixtures::VETTURA_TEST_1 constant
        $this->addReference(self::VETTURA_TEST_1, $vettura);
    }

    public function getDependencies(): array
    {
        return [
            AnagraficaFixtures::class,
        ];
    }
}