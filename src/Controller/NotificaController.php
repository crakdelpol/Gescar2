<?php

namespace App\Controller;

use App\Entity\Anagrafica;
use App\Entity\Notifica;
use App\Form\NotificaType;
use App\Repository\AnagraficaRepository;
use App\Repository\NotificaRepository;
use App\Service\NotificaInvioService;
use App\Service\NotificaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifica', name: 'app_notifica_')]
final class NotificaController extends AbstractController
{
    public function __construct(
        private readonly NotificaService     $notificaService,
        private readonly NotificaInvioService $invioService,
    ) {}

    /**
     * Lista tutte le notifiche, con filtro opzionale per cliente.
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        NotificaRepository $repo,
        Request $request
    ): Response {
        $giorni    = (int) $request->query->get('giorni', 30);
        $notifiche = $this->notificaService->getRecenti($giorni);

        return $this->render('notifica/index.html.twig', [
            'notifiche' => $notifiche,
            'giorni'    => $giorni,
        ]);
    }

    /**
     * Nuova notifica (standalone, senza cliente pre-selezionato).
     */
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $notifica = new Notifica();
        $form     = $this->createForm(NotificaType::class, $notifica);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notifica->setUtente($this->getUser());
            $em->persist($notifica);
            $em->flush();
            $this->addFlash('success', 'Notifica registrata.');
            return $this->redirectToRoute('app_notifica_index');
        }

        return $this->render('notifica/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Nuova notifica pre-compilata per un cliente specifico.
     */
    #[Route('/new/{id}', name: 'new_per_cliente', methods: ['GET', 'POST'])]
    public function newPerCliente(
        Anagrafica $anagrafica,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $notifica = new Notifica();
        $notifica->setAnagrafica($anagrafica);

        $form = $this->createForm(NotificaType::class, $notifica);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $notifica->setUtente($this->getUser());
            $em->persist($notifica);
            $em->flush();
            $this->addFlash('success', 'Notifica registrata per ' . $anagrafica->getCognome() . '.');
            return $this->redirectToRoute('app_anagrafica_show', ['id' => $anagrafica->getId()]);
        }

        return $this->render('notifica/new.html.twig', [
            'form'       => $form->createView(),
            'anagrafica' => $anagrafica,
        ]);
    }

    /**
     * Dettaglio notifica.
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Notifica $notifica): Response
    {
        return $this->render('notifica/show.html.twig', [
            'notifica' => $notifica,
        ]);
    }

    /**
     * Modifica esito/note di una notifica esistente.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Notifica $notifica,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(NotificaType::class, $notifica);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Notifica aggiornata.');
            return $this->redirectToRoute('app_notifica_index');
        }

        return $this->render('notifica/edit.html.twig', [
            'notifica' => $notifica,
            'form'     => $form->createView(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INVIO RAPIDO DA SCADENZE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Valida il telefono, registra la notifica WhatsApp e restituisce l'URL wa.me.
     * Chiamato in AJAX dalla pagina scadenze (GET).
     *
     * Params: anaId (int), tipo (string), data (Y-m-d), targa (string|null)
     */
    #[Route('/invia/whatsapp', name: 'invia_whatsapp', methods: ['GET'])]
    public function inviaWhatsApp(
        Request             $request,
        AnagraficaRepository $anaRepo,
    ): JsonResponse {
        $anaId = (int) $request->query->get('anaId');
        $tipo  = (string) $request->query->get('tipo');
        $data  = $request->query->get('data');
        $targa = $request->query->get('targa');

        $anagrafica = $anaRepo->find($anaId);
        if (!$anagrafica) {
            return $this->json(['error' => 'Cliente non trovato.'], 404);
        }

        $dataObj = $data ? \DateTime::createFromFormat('Y-m-d', $data) : null;

        $result = $this->invioService->generaUrlWhatsApp($anagrafica, $tipo, $dataObj ?: null, $targa ?: null);

        if (isset($result['error'])) {
            return $this->json(['error' => $result['error']], 422);
        }

        // Registra la notifica nel DB
        $this->invioService->logWhatsApp($anagrafica, $tipo, $targa ?: null);

        return $this->json(['url' => $result['url']]);
    }

    /**
     * Invia l'email di notifica e registra l'invio.
     * Chiamato con POST dalla pagina scadenze.
     *
     * Params form: anaId, tipo, data (Y-m-d), targa, _token
     */
    #[Route('/invia/email', name: 'invia_email', methods: ['POST'])]
    public function inviaEmail(
        Request             $request,
        AnagraficaRepository $anaRepo,
    ): Response {
        $anaId = (int) $request->request->get('anaId');
        $tipo  = (string) $request->request->get('tipo');
        $data  = $request->request->get('data');
        $targa = $request->request->get('targa');
        $ref   = $request->request->get('_ref', $this->generateUrl('app_scadenze_home'));

        if (!$this->isCsrfTokenValid('notifica_email', $request->request->get('_token'))) {
            $this->addFlash('error', 'Token non valido. Riprova.');
            return $this->redirect($ref);
        }

        $anagrafica = $anaRepo->find($anaId);
        if (!$anagrafica) {
            $this->addFlash('error', 'Cliente non trovato.');
            return $this->redirect($ref);
        }

        $dataObj = $data ? \DateTime::createFromFormat('Y-m-d', $data) : null;

        try {
            $this->invioService->inviaEmail($anagrafica, $tipo, $dataObj ?: null, $targa ?: null);
            $this->addFlash('success', sprintf(
                'Email inviata a %s %s (%s).',
                $anagrafica->getCognome(),
                $anagrafica->getNome(),
                $anagrafica->getEmail()
            ));
        } catch (\RuntimeException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Errore durante l\'invio dell\'email. Riprova o controlla la configurazione SMTP.');
        }

        return $this->redirect($ref);
    }

    /**
     * Eliminazione notifica.
     */
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Notifica $notifica,
        EntityManagerInterface $em
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $notifica->getId(), $request->getPayload()->getString('_token'))) {
            $em->remove($notifica);
            $em->flush();
            $this->addFlash('success', 'Notifica eliminata.');
        }
        return $this->redirectToRoute('app_notifica_index');
    }
}
