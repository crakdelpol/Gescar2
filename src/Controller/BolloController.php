<?php

namespace App\Controller;

use App\Entity\Bollo;
use App\Form\BolloType;
use App\Repository\BolloRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bollo')]
final class BolloController extends AbstractController
{
    #[Route(name: 'app_bollo_index', methods: ['GET'])]
    public function index(BolloRepository $bolloRepository): Response
    {
        return $this->render('bollo/index.html.twig', [
            'bollos' => $bolloRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_bollo_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bollo = new Bollo();
        $form = $this->createForm(BolloType::class, $bollo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bollo);
            $entityManager->flush();

            return $this->redirectToRoute('app_bollo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bollo/new.html.twig', [
            'bollo' => $bollo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bollo_show', methods: ['GET'])]
    public function show(Bollo $bollo): Response
    {
        return $this->render('bollo/show.html.twig', [
            'bollo' => $bollo,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bollo_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bollo $bollo, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BolloType::class, $bollo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_bollo_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bollo/edit.html.twig', [
            'bollo' => $bollo,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bollo_delete', methods: ['POST'])]
    public function delete(Request $request, Bollo $bollo, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bollo->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($bollo);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_bollo_index', [], Response::HTTP_SEE_OTHER);
    }
}
