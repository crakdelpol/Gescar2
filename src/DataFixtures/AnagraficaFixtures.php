<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Anagrafica;

class AnagraficaFixtures extends Fixture
{
    public const CLIENTE_TEST_1 = 'test-1';

    public function load(ObjectManager $manager): void
    {
        $anagrafica = new Anagrafica();
        $anagrafica->setNome('Nome');
        $anagrafica->setCognome('Cognome');
        $anagrafica->setTelefono(mt_rand(100000, 999999));
        $anagrafica->setNote('Angrafica di test');
        $anagrafica->setLuogoNascita('Savona');
        $anagrafica->setResidenza('Via Roma');
        $anagrafica->setCodiceFiscale('TSTCRL90L11I480D');
        $anagrafica->setEmail('testtest@test.it');
        $anagrafica->setTipo('Privato');
        $anagrafica->setCodiceDestinatario('111000010');
        $anagrafica->setPartitaIva('99999999');
        $anagrafica->setSedeLegale('Sede legale');

        $manager->persist($anagrafica);
        $manager->flush();

        // other fixtures can get this object using the AnagraficaFixtures::CLIENTE_TEST_1 constant
        $this->addReference(self::CLIENTE_TEST_1, $anagrafica);
    }
}