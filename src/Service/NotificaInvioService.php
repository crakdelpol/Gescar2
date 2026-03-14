<?php

namespace App\Service;

use App\Entity\Anagrafica;
use App\Entity\Notifica;
use App\Repository\NotificaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

/**
 * Gestisce l'invio effettivo delle notifiche ai clienti (email e WhatsApp)
 * e la registrazione automatica nel registro notifiche.
 */
class NotificaInvioService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly NotificaRepository     $notificaRepository,
        private readonly MailerInterface        $mailer,
        private readonly Environment            $twig,
        private readonly string                 $mittente,
        private readonly string                 $nomeCentro,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // WHATSAPP
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalizza un numero di telefono al formato internazionale +39XXXXXXXXXX.
     * Restituisce null se il numero non è valido.
     */
    public function normalizzaTelefono(string $raw): ?string
    {
        // Rimuove spazi, trattini, punti, parentesi
        $tel = preg_replace('/[\s\-\.\(\)\/]/', '', $raw);

        // Gestisce prefissi già presenti
        if (str_starts_with($tel, '0039')) {
            $tel = '+39' . substr($tel, 4);
        } elseif (str_starts_with($tel, '39') && !str_starts_with($tel, '+')) {
            $tel = '+39' . substr($tel, 2);
        } elseif (!str_starts_with($tel, '+')) {
            $tel = '+39' . $tel;
        }

        // Validazione: +39 seguito da 9-10 cifre
        // Numeri mobili italiani: 3xx-XXXXXXX (10 cifre dopo prefisso)
        // Numeri fissi:           0x-XXXXXXXX (9-10 cifre dopo prefisso)
        if (preg_match('/^\+39\d{9,10}$/', $tel)) {
            return $tel;
        }

        return null;
    }

    /**
     * Genera l'URL wa.me con il messaggio pre-compilato.
     * Restituisce ['url' => '...'] oppure ['error' => '...'].
     */
    public function generaUrlWhatsApp(
        Anagrafica         $anagrafica,
        string             $tipoScadenza,
        ?\DateTimeInterface $dataScadenza,
        ?string            $targa = null,
    ): array {
        $tel = $anagrafica->getTelefono();

        if (empty($tel)) {
            return ['error' => 'Nessun numero di telefono registrato per questo cliente.'];
        }

        $numero = $this->normalizzaTelefono($tel);

        if ($numero === null) {
            return [
                'error' => sprintf(
                    'Il numero "%s" non è valido. Correggi il telefono nell\'anagrafica del cliente.',
                    $tel
                ),
            ];
        }

        $messaggio = $this->buildMessaggioWhatsApp($anagrafica, $tipoScadenza, $dataScadenza, $targa);
        $url       = 'https://wa.me/' . ltrim($numero, '+') . '?text=' . rawurlencode($messaggio);

        return ['url' => $url, 'numero' => $numero];
    }

    /**
     * Testo del messaggio WhatsApp, personalizzato per tipo scadenza.
     */
    private function buildMessaggioWhatsApp(
        Anagrafica         $anagrafica,
        string             $tipo,
        ?\DateTimeInterface $data,
        ?string            $targa,
    ): string {
        $nome  = $anagrafica->getCognome() . ' ' . $anagrafica->getNome();
        $data  = $data ? $data->format('d/m/Y') : 'prossimamente';
        $veicolo = $targa ? " (targa $targa)" : '';

        $corpo = match ($tipo) {
            Notifica::TIPO_REVISIONE     => "La revisione del suo veicolo{$veicolo} scade il *{$data}*. La invitiamo a prenotare al più presto.",
            Notifica::TIPO_ASSICURAZIONE => "L'assicurazione del suo veicolo{$veicolo} scade il *{$data}*. Le consigliamo di rinnovarla prima della scadenza.",
            Notifica::TIPO_BOLLO         => "Il bollo del suo veicolo{$veicolo} scade il *{$data}*. Ricordi di provvedere al pagamento.",
            Notifica::TIPO_PATENTE       => "La sua patente di guida scade il *{$data}*. La invitiamo a contattarci per il rinnovo.",
            default                      => "Ha una scadenza il *{$data}* che richiede la sua attenzione.",
        };

        return "Gentile {$nome},\n\n{$corpo}\n\nPer informazioni o prenotazioni non esiti a contattarci.\n\n*{$this->nomeCentro}*";
    }

    /**
     * Registra una notifica WhatsApp nel database.
     */
    public function logWhatsApp(
        Anagrafica $anagrafica,
        string     $tipoScadenza,
        ?string    $targa = null,
    ): Notifica {
        return $this->log($anagrafica, $tipoScadenza, Notifica::CANALE_WHATSAPP, Notifica::ESITO_INVIATA, $targa);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EMAIL
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Invia l'email di notifica scadenza e registra l'invio.
     * Lancia un'eccezione se l'email del cliente non è presente.
     */
    public function inviaEmail(
        Anagrafica         $anagrafica,
        string             $tipoScadenza,
        ?\DateTimeInterface $dataScadenza,
        ?string            $targa = null,
    ): Notifica {
        if (empty($anagrafica->getEmail())) {
            throw new \RuntimeException(
                'Nessun indirizzo email registrato per ' . $anagrafica->getCognome() . ' ' . $anagrafica->getNome() . '.'
            );
        }

        $oggettoMap = [
            Notifica::TIPO_REVISIONE     => 'Promemoria: revisione veicolo in scadenza',
            Notifica::TIPO_ASSICURAZIONE => 'Promemoria: assicurazione veicolo in scadenza',
            Notifica::TIPO_BOLLO         => 'Promemoria: bollo veicolo in scadenza',
            Notifica::TIPO_PATENTE       => 'Promemoria: patente di guida in scadenza',
        ];

        $html = $this->twig->render('email/notifica_scadenza.html.twig', [
            'anagrafica'  => $anagrafica,
            'tipo'        => $tipoScadenza,
            'data'        => $dataScadenza,
            'targa'       => $targa,
            'nome_centro' => $this->nomeCentro,
        ]);

        $email = (new Email())
            ->from(new Address($this->mittente, $this->nomeCentro))
            ->to(new Address($anagrafica->getEmail(), $anagrafica->getCognome() . ' ' . $anagrafica->getNome()))
            ->subject($oggettoMap[$tipoScadenza] ?? 'Promemoria scadenza')
            ->html($html);

        $this->mailer->send($email);

        return $this->log($anagrafica, $tipoScadenza, Notifica::CANALE_EMAIL, Notifica::ESITO_INVIATA, $targa);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HELPER COMUNE
    // ─────────────────────────────────────────────────────────────────────────

    private function log(
        Anagrafica $anagrafica,
        string     $tipoScadenza,
        string     $canale,
        string     $esito,
        ?string    $targa,
    ): Notifica {
        $notifica = new Notifica();
        $notifica->setAnagrafica($anagrafica);
        $notifica->setTipoScadenza($tipoScadenza);
        $notifica->setCanale($canale);
        $notifica->setEsito($esito);
        if ($targa) {
            $notifica->setNote("Veicolo targa: $targa");
        }

        $this->em->persist($notifica);
        $this->em->flush();

        return $notifica;
    }
}
