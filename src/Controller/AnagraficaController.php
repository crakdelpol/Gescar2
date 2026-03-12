<?php

namespace App\Controller;

use App\Entity\Anagrafica;
use App\Repository\AnagraficaRepository;
use App\Form\AnagraficaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/anagrafica')]
final class AnagraficaController extends AbstractController
{
    #[Route(name: 'app_anagrafica_index', methods: ['GET'])]
    public function index(Request $request, AnagraficaRepository $anagraficaRepository): Response
    {
        $q = trim($request->query->get('q', ''));

        $anagrafiche = $q
            ? $anagraficaRepository->searchGlobale($q)
            : $anagraficaRepository->findAll();

        return $this->render('anagrafica/index.html.twig', [
            'anagrafiche' => $anagrafiche,
            'q'           => $q,
        ]);
    }

    #[Route('/new', name: 'app_anagrafica_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $anagrafica = new Anagrafica();
        $form = $this->createForm(AnagraficaType::class, $anagrafica);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($anagrafica);
            $entityManager->flush();        

            return $this->redirectToRoute('app_anagrafica_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('anagrafica/new.html.twig', [
            'anagrafica' => $anagrafica,
            'form' => $form,
        ]);
    }

    #[Route('{id}', name: 'app_anagrafica_show', methods: ['GET'])]
    public function show(Anagrafica $anagrafica): Response
    {
        return $this->render('anagrafica/show.html.twig', [
            'anagrafica' => $anagrafica,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_anagrafica_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Anagrafica $anagrafica, EntityManagerInterface $entityManager): Response 
    {

        $form = $this->createForm(AnagraficaType::class, $anagrafica);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_anagrafica_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('anagrafica/edit.html.twig', [
            'anagrafica' => $anagrafica,
            'form' => $form,
        ]);

    }

    #[Route('/{id}', name: 'app_anagrafica_delete', methods: ['POST'])]
    public function deleteAction(Request $request, Anagrafica $anagrafica, EntityManagerInterface $entityManager)
    {
        if($this->isCsrfTokenValid('delete'.$anagrafica->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($anagrafica);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_anagrafica_index', [], Response::HTTP_SEE_OTHER);
    }
}
