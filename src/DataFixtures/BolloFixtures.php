<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Bollo;
use App\Entity\Vettura;
use DateTime;

class BolloFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $bollo = new Bollo();
        $bollo->setDataScadenzaBollo(new DateTime('2025-07-22'));
        $bollo->setNote('Note bollo  ');
        // this reference returns the Vettura object created in VetturaFixtures
        $bollo->setVettura($this->getReference(VetturaFixtures::VETTURA_TEST_1, Vettura::class));

        $manager->persist($bollo);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VetturaFixtures::class,
        ];
    }
}