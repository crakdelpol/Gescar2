<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard', name: 'app_dashboard_')]
class DashboardController extends AbstractController
{
    /**
     * La dashboard è stata incorporata nella pagina Scadenze.
     * Questo redirect garantisce la retrocompatibilità con eventuali
     * link salvati o bookmark al percorso /dashboard/.
     */
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_scadenze_home', [], Response::HTTP_MOVED_PERMANENTLY);
    }
}
