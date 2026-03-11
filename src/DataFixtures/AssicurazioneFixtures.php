<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Assicurazione;
use App\Entity\Vettura;
use DateTime;

class AssicurazioneFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $assicurazione = new Assicurazione();
        $assicurazione->setDataScadenzaAssicurazione(new DateTime('2025-07-22'));
        $assicurazione->setNote('Note Assicurazione  ');
        // this reference returns the Vettura object created in VetturaFixtures
        $assicurazione->setVettura($this->getReference(VetturaFixtures::VETTURA_TEST_1, Vettura::class));

        $manager->persist($assicurazione);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VetturaFixtures::class,
        ];
    }
}