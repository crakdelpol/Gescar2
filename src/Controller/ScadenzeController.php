<?php

namespace App\Controller;

use App\Entity\Assicurazione;
use App\Entity\Bollo;
use App\Entity\Patente;
use App\Entity\Vettura;
use App\Form\ScadenzaType;
use App\Service\ScadenzaService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ScadenzeController extends AbstractController
{
    public function __construct(
        private readonly ScadenzaService $scadenzaService
    ) {}

    /**
     * Home: semaforo immediato + scadenze del periodo corrente (oggi → +30 giorni).
     */
    #[Route('/', name: 'app_scadenze_home')]
    public function index(EntityManagerInterface $em): Response
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
            'bolli'           => $this->queryBolli($dataScadenza, $aDataScadenza, $em),
            'assicurazioni'   => $this->queryAssicurazioni($dataScadenza, $aDataScadenza, $em),
            'patenti'         => $this->queryPatenti($dataScadenza, $aDataScadenza, $em),
            'vetture'         => $this->queryRevisioni($dataScadenza, $aDataScadenza, $em),
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
        string $dataScadenza,
        string $aDataScadenza,
        Request $request,
        EntityManagerInterface $em
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

        return $this->render('scadenze/index.html.twig', [
            'bolli'           => $this->queryBolli($dataScadenza, $aDataScadenza, $em),
            'assicurazioni'   => $this->queryAssicurazioni($dataScadenza, $aDataScadenza, $em),
            'patenti'         => $this->queryPatenti($dataScadenza, $aDataScadenza, $em),
            'vetture'         => $this->queryRevisioni($dataScadenza, $aDataScadenza, $em),
            'dataScadenza'    => $dataScadenza,
            'aDataScadenza'   => $aDataScadenza,
            'search_form'     => $form->createView(),
            'scadenzaService' => $this->scadenzaService,
            'sommario'        => $this->scadenzaService->getSommarioDashboard(),
        ]);
    }

    // ── Query private ────────────────────────────────────────────────────────

    private function queryBolli(string $da, string $a, EntityManagerInterface $em): array
    {
        return $em->getRepository(Bollo::class)
            ->createQueryBuilder('b')
            ->join('b.vettura', 'v')
            ->join('v.intestatario', 'ana')
            ->where('b.dataScadenzaBollo BETWEEN :da AND :a')
            ->setParameters(new ArrayCollection([
                new Parameter('da', $da),
                new Parameter('a', $a),
            ]))
            ->orderBy('b.dataScadenzaBollo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function queryAssicurazioni(string $da, string $a, EntityManagerInterface $em): array
    {
        return $em->getRepository(Assicurazione::class)
            ->createQueryBuilder('ass')
            ->join('ass.vettura', 'v')
            ->join('v.intestatario', 'ana')
            ->where('ass.dataScadenzaAssicurazione BETWEEN :da AND :a')
            ->setParameters(new ArrayCollection([
                new Parameter('da', $da),
                new Parameter('a', $a),
            ]))
            ->orderBy('ass.dataScadenzaAssicurazione', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function queryPatenti(string $da, string $a, EntityManagerInterface $em): array
    {
        return $em->getRepository(Patente::class)
            ->createQueryBuilder('p')
            ->join('p.intestatario', 'ana')
            ->where('p.dataScadenzaPatente BETWEEN :da AND :a')
            ->setParameters(new ArrayCollection([
                new Parameter('da', $da),
                new Parameter('a', $a),
            ]))
            ->orderBy('p.dataScadenzaPatente', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function queryRevisioni(string $da, string $a, EntityManagerInterface $em): array
    {
        return $em->getRepository(Vettura::class)
            ->createQueryBuilder('v')
            ->join('v.intestatario', 'ana')
            ->where('v.dataScadenzaRevisione BETWEEN :da AND :a')
            ->andWhere('v.esenteRevisione = false')
            ->setParameters(new ArrayCollection([
                new Parameter('da', $da),
                new Parameter('a', $a),
            ]))
            ->orderBy('v.dataScadenzaRevisione', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
