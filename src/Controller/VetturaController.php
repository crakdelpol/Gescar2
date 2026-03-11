<?php

namespace App\Controller;

use App\Entity\Vettura;
use App\Form\VetturaType;
use App\Repository\VetturaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/vettura')]
final class VetturaController extends AbstractController
{
    #[Route(name: 'app_vettura_index', methods: ['GET'])]
    public function index(VetturaRepository $vetturaRepository): Response
    {
        return $this->render('vettura/index.html.twig', [
            'vetturas' => $vetturaRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_vettura_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $vettura = new Vettura();
        $form = $this->createForm(VetturaType::class, $vettura);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($vettura);
            $entityManager->flush();

            return $this->redirectToRoute('app_vettura_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vettura/new.html.twig', [
            'vettura' => $vettura,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vettura_show', methods: ['GET'])]
    public function show(Vettura $vettura): Response
    {
        return $this->render('vettura/show.html.twig', [
            'vettura' => $vettura,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_vettura_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Vettura $vettura, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(VetturaType::class, $vettura);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_vettura_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('vettura/edit.html.twig', [
            'vettura' => $vettura,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_vettura_delete', methods: ['POST'])]
    public function delete(Request $request, Vettura $vettura, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$vettura->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($vettura);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_vettura_index', [], Response::HTTP_SEE_OTHER);
    }
}
