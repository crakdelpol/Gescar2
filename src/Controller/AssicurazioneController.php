<?php

namespace App\Controller;

use App\Entity\Assicurazione;
use App\Form\AssicurazioneType;
use App\Repository\AssicurazioneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assicurazione')]
final class AssicurazioneController extends AbstractController
{
    #[Route(name: 'app_assicurazione_index', methods: ['GET'])]
    public function index(AssicurazioneRepository $assicurazioneRepository): Response
    {
        return $this->render('assicurazione/index.html.twig', [
            'assicuraziones' => $assicurazioneRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_assicurazione_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $assicurazione = new Assicurazione();
        $form = $this->createForm(AssicurazioneType::class, $assicurazione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($assicurazione);
            $entityManager->flush();

            return $this->redirectToRoute('app_assicurazione_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assicurazione/new.html.twig', [
            'assicurazione' => $assicurazione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_assicurazione_show', methods: ['GET'])]
    public function show(Assicurazione $assicurazione): Response
    {
        return $this->render('assicurazione/show.html.twig', [
            'assicurazione' => $assicurazione,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_assicurazione_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Assicurazione $assicurazione, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AssicurazioneType::class, $assicurazione);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_assicurazione_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('assicurazione/edit.html.twig', [
            'assicurazione' => $assicurazione,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_assicurazione_delete', methods: ['POST'])]
    public function delete(Request $request, Assicurazione $assicurazione, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$assicurazione->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($assicurazione);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_assicurazione_index', [], Response::HTTP_SEE_OTHER);
    }
}
