<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Anagrafica;

class AnagraficaFixtures extends Fixture
{
    // Costanti per i riferimenti usati dalle altre fixtures
    public const CLIENTI = [
        'rossi-mario', 'bianchi-lucia', 'ferrari-giuseppe', 'esposito-anna',
        'conti-roberto', 'gallo-francesca', 'autoservice-srl', 'trasporti-nord',
    ];

    private array $dati = [
        ['nome' => 'Mario',     'cognome' => 'Rossi',       'tipo' => 'privato',  'cf' => 'RSSMRA80A01I480U', 'tel' => '3201234567', 'email' => 'mario.rossi@email.it',     'residenza' => 'Via Roma 12, Savona'],
        ['nome' => 'Lucia',     'cognome' => 'Bianchi',     'tipo' => 'privato',  'cf' => 'BNCLCU85M41I480P', 'tel' => '3357654321', 'email' => 'lucia.bianchi@email.it',   'residenza' => 'Corso Italia 5, Savona'],
        ['nome' => 'Giuseppe',  'cognome' => 'Ferrari',     'tipo' => 'privato',  'cf' => 'FRRGPP75D12I480X', 'tel' => '3481239876', 'email' => 'g.ferrari@email.it',       'residenza' => 'Via Mazzini 33, Albenga'],
        ['nome' => 'Anna',      'cognome' => 'Esposito',    'tipo' => 'privato',  'cf' => 'SPSNNA90P52I480K', 'tel' => '3661234000', 'email' => 'anna.esposito@email.it',   'residenza' => 'Piazza Mameli 2, Finale Ligure'],
        ['nome' => 'Roberto',   'cognome' => 'Conti',       'tipo' => 'privato',  'cf' => 'CNTRRT70C11I480Q', 'tel' => '3291122334', 'email' => 'r.conti@email.it',         'residenza' => 'Via Aurelia 88, Loano'],
        ['nome' => 'Francesca', 'cognome' => 'Gallo',       'tipo' => 'privato',  'cf' => 'GLLFNC88H61I480Z', 'tel' => '3405566778', 'email' => 'fra.gallo@email.it',       'residenza' => 'Viale Nizza 14, Savona'],
        ['nome' => 'Auto Service', 'cognome' => 'Srl',      'tipo' => 'azienda',  'piva' => '01234567890',    'tel' => '019123456',  'email' => 'info@autoservice.it',      'sede' => 'Via Industriale 10, Vado Ligure'],
        ['nome' => 'Trasporti Nord', 'cognome' => 'Srl',    'tipo' => 'azienda',  'piva' => '09876543210',    'tel' => '019654321',  'email' => 'admin@trasportinord.it',   'sede' => 'Zona Industriale 4, Cairo Montenotte'],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach ($this->dati as $i => $d) {
            $a = new Anagrafica();
            $a->setNome($d['nome']);
            $a->setCognome($d['cognome']);
            $a->setTipo($d['tipo']);
            $a->setTelefono($d['tel']);
            $a->setEmail($d['email']);

            if ($d['tipo'] === 'privato') {
                $a->setCodiceFiscale($d['cf']);
                $a->setResidenza($d['residenza']);
                $a->setLuogoNascita('Savona');
            } else {
                $a->setPartitaIva($d['piva']);
                $a->setSedeLegale($d['sede']);
            }

            $manager->persist($a);
            $this->addReference(self::CLIENTI[$i], $a);
        }

        $manager->flush();
    }
}
