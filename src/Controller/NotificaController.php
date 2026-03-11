<?php

namespace App\Controller;

use App\Entity\Anagrafica;
use App\Entity\Notifica;
use App\Form\NotificaType;
use App\Repository\NotificaRepository;
use App\Service\NotificaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notifica', name: 'app_notifica_')]
final class NotificaController extends AbstractController
{
    public function __construct(
        private readonly NotificaService $notificaService
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
