<?php

namespace App\Controller;

use App\Entity\Patente;
use App\Form\PatenteType;
use App\Repository\PatenteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/patente')]
final class PatenteController extends AbstractController
{
    #[Route(name: 'app_patente_index', methods: ['GET'])]
    public function index(PatenteRepository $patenteRepository): Response
    {
        return $this->render('patente/index.html.twig', [
            'patentes' => $patenteRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_patente_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $patente = new Patente();
        $form = $this->createForm(PatenteType::class, $patente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($patente);
            $entityManager->flush();

            return $this->redirectToRoute('app_patente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('patente/new.html.twig', [
            'patente' => $patente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_patente_show', methods: ['GET'])]
    public function show(Patente $patente): Response
    {
        return $this->render('patente/show.html.twig', [
            'patente' => $patente,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_patente_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Patente $patente, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PatenteType::class, $patente);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_patente_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('patente/edit.html.twig', [
            'patente' => $patente,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_patente_delete', methods: ['POST'])]
    public function delete(Request $request, Patente $patente, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$patente->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($patente);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_patente_index', [], Response::HTTP_SEE_OTHER);
    }
}
