<?php

namespace App\Controller;

use App\Form\ScadenzaType;
use App\Repository\AssicurazioneRepository;
use App\Repository\BolloRepository;
use App\Repository\PatenteRepository;
use App\Repository\VetturaRepository;
use App\Service\ScadenzaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ScadenzeController extends AbstractController
{
    public function __construct(
        private readonly ScadenzaService         $scadenzaService,
        private readonly BolloRepository         $bolloRepo,
        private readonly AssicurazioneRepository  $assicurazioneRepo,
        private readonly PatenteRepository        $patenteRepo,
        private readonly VetturaRepository        $vetturaRepo,
    ) {}

    /**
     * Home: semaforo immediato + scadenze del periodo corrente (oggi → +30 giorni).
     */
    #[Route('/', name: 'app_scadenze_home')]
    public function index(): Response
    {
        $dataScadenza  = date('Y-m-d');
        $aDataScadenza = (new \DateTime())->modify('+1 month')->format('Y-m-d');

        $form = $this->createForm(ScadenzaType::class, [
            'dataScadenza'  => new \DateTime($dataScadenza),
            'aDataScadenza' => new \DateTime($aDataScadenza),
        ], [
            'action' => $this->generateUrl('app_scadenze_index', [
                'dataScadenza'  => $dataScadenza,
                'aDataScadenza' => $aDataScadenza,
            ]),
            'method' => 'POST',
        ]);

        return $this->render('scadenze/index.html.twig', [
            'bolli'           => $this->bolloRepo->findInRange(new \DateTime($dataScadenza), new \DateTime($aDataScadenza)),
            'assicurazioni'   => $this->assicurazioneRepo->findInRange(new \DateTime($dataScadenza), new \DateTime($aDataScadenza)),
            'patenti'         => $this->patenteRepo->findInRange(new \DateTime($dataScadenza), new \DateTime($aDataScadenza)),
            'vetture'         => $this->vetturaRepo->findRevisioniInRange(new \DateTime($dataScadenza), new \DateTime($aDataScadenza)),
            'dataScadenza'    => $dataScadenza,
            'aDataScadenza'   => $aDataScadenza,
            'search_form'     => $form->createView(),
            'scadenzaService' => $this->scadenzaService,
            'sommario'        => $this->scadenzaService->getSommarioDashboard(),
        ]);
    }

    /**
     * Ricerca per periodo: semaforo sempre visibile + risultati filtrati.
     */
    #[Route('/scadenze/{dataScadenza}/{aDataScadenza}', name: 'app_scadenze_index')]
    public function search(
        string  $dataScadenza,
        string  $aDataScadenza,
        Request $request,
    ): Response {
        $form = $this->createForm(ScadenzaType::class, [
            'dataScadenza'  => new \DateTime($dataScadenza),
            'aDataScadenza' => new \DateTime($aDataScadenza),
        ], [
            'action' => $this->generateUrl('app_scadenze_index', [
                'dataScadenza'  => $dataScadenza,
                'aDataScadenza' => $aDataScadenza,
            ]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            return $this->redirectToRoute('app_scadenze_index', [
                'dataScadenza'  => $data['dataScadenza']->format('Y-m-d'),
                'aDataScadenza' => $data['aDataScadenza']->format('Y-m-d'),
            ]);
        }

        $da = new \DateTime($dataScadenza);
        $a  = new \DateTime($aDataScadenza);

        return $this->render('scadenze/index.html.twig', [
            'bolli'           => $this->bolloRepo->findInRange($da, $a),
            'assicurazioni'   => $this->assicurazioneRepo->findInRange($da, $a),
            'patenti'         => $this->patenteRepo->findInRange($da, $a),
            'vetture'         => $this->vetturaRepo->findRevisioniInRange($da, $a),
            'dataScadenza'    => $dataScadenza,
            'aDataScadenza'   => $aDataScadenza,
            'search_form'     => $form->createView(),
            'scadenzaService' => $this->scadenzaService,
            'sommario'        => $this->scadenzaService->getSommarioDashboard(),
        ]);
    }
}
