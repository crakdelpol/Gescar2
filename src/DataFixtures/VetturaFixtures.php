<?php

namespace App\DataFixtures;

use App\Entity\Anagrafica;
use App\Entity\Vettura;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class VetturaFixtures extends Fixture implements DependentFixtureInterface
{
    // Riferimenti per le altre fixtures
    public const VETTURE = [
        'v-fiat-punto', 'v-vw-golf', 'v-peugeot-308', 'v-bmw-serie3',
        'v-ford-focus', 'v-renault-clio', 'v-toyota-yaris', 'v-opel-corsa',
        'v-alfa-giulia', 'v-mercedes-a', 'v-fiat-ducato', 'v-ford-transit',
    ];

    private array $dati = [
        // targa,       telaio,      tipo,          carburante,  marca,      modello,      ultima_rev, scad_rev,   intestatario
        ['SV001AB', 'ZFA18800000111', 'autovettura', 'benzina',  'Fiat',     'Punto',      '-2 years', '-15 days', 'rossi-mario'],      // SCADUTA
        ['SV002CD', 'WVWZZZ1JZXW000', 'autovettura', 'gasolio',  'Volkswagen','Golf',     '-1 year',  '+10 days', 'bianchi-lucia'],    // IN SCADENZA
        ['SV003EF', 'VF30C9HZB00001', 'autovettura', 'benzina',  'Peugeot',  '308',       '-1 year',  '+40 days', 'ferrari-giuseppe'], // IN AVVICINAMENTO
        ['SV004GH', 'WBA3A5C51CF000', 'autovettura', 'gasolio',  'BMW',      'Serie 3',   '-1 year',  '+120 days','esposito-anna'],    // OK
        ['SV005IJ', '1FAFP34N13W000', 'autovettura', 'benzina',  'Ford',     'Focus',     '-2 years', '+5 days',  'conti-roberto'],    // IN SCADENZA
        ['SV006KL', 'VF1BB1A0H00001', 'autovettura', 'benzina',  'Renault',  'Clio',      '-3 years', '-30 days', 'gallo-francesca'],  // SCADUTA
        ['SV007MN', 'JTDKB20U903000', 'autovettura', 'benzina',  'Toyota',   'Yaris',     '-1 year',  '+55 days', 'rossi-mario'],     // IN AVVICINAMENTO
        ['SV008OP', 'W0L000051T2000', 'autovettura', 'benzina',  'Opel',     'Corsa',     '-1 year',  '+200 days','bianchi-lucia'],    // OK
        ['SV009QR', 'ZAR93600007000', 'autovettura', 'gasolio',  'Alfa Romeo','Giulia',   '-1 year',  '+25 days', 'ferrari-giuseppe'], // IN SCADENZA
        ['SV010ST', 'WDD1760312J000', 'autovettura', 'gasolio',  'Mercedes', 'Classe A',  '-2 years', '-5 days',  'esposito-anna'],    // SCADUTA
        ['SV011UV', 'ZFA25000000222', 'autocarro',   'gasolio',  'Fiat',     'Ducato',    '-1 year',  '+15 days', 'autoservice-srl'],  // IN SCADENZA
        ['SV012WX', '1FTBF2A60EEA00', 'autocarro',  'gasolio',  'Ford',     'Transit',   '-2 years', '+70 days', 'trasporti-nord'],   // OK
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->dati as $i => $d) {
            $v = new Vettura();
            $v->setTarga($d[0]);
            $v->setNumeroTelaio($d[1]);
            $v->setTipo($d[2]);
            $v->setCarburante($d[3]);
            $v->setMarca($d[4]);
            $v->setModello($d[5]);
            $v->setDataUltimaRevisione(new \DateTime($d[6]));
            $v->setDataScadenzaRevisione(new \DateTime($d[7]));
            $v->setIntestatario($this->getReference($d[8], Anagrafica::class));

            $manager->persist($v);
            $this->addReference(self::VETTURE[$i], $v);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [AnagraficaFixtures::class];
    }
}
