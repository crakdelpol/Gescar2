<?php

namespace App\Controller;

use App\Service\ScadenzaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dashboard', name: 'app_dashboard_')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly ScadenzaService $scadenzaService
    ) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $sommario = $this->scadenzaService->getSommarioDashboard();

        return $this->render('dashboard/index.html.twig', [
            'revisioni_scadute'       => $sommario['revisioni_scadute'],
            'revisioni_30gg'          => $sommario['revisioni_30gg'],
            'assicurazioni_30gg'      => $sommario['assicurazioni_30gg'],
            'patenti_60gg'            => $sommario['patenti_60gg'],
            'bolli_mese_corrente'     => $sommario['bolli_mese_corrente'],
        ]);
    }
}
