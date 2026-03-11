<?php

namespace App\Controller;

use App\Entity\Assicurazione;
use App\Entity\Bollo;
use App\Entity\Patente;
use App\Entity\Vettura;
use App\Form\ScadenzaType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ScadenzeController extends AbstractController
{
    #[Route('/', name: 'app_scadenze_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $dataScadenza = date('Y-m-d');
        $aDataScadenza = date('Y-m-d', strtotime("+1 months", strtotime($dataScadenza)));

        $form = $this->createForm(ScadenzaType::class, null, array(
            'action' => $this->generateUrl('app_scadenze_index', array(
                'dataScadenza' => $dataScadenza,
                'aDataScadenza' => $aDataScadenza
            )),
            'method' => 'POST',
            'attr' => array('role' => 'form'),
        ));

        $bolli = $this->getBolliScaduti($dataScadenza, $aDataScadenza, $entityManager);
        $assicurazioni = $this->getAssicurazioniScadute($dataScadenza, $aDataScadenza, $entityManager);
        $patenti = $this->getPatentiScadute($dataScadenza, $aDataScadenza, $entityManager);
        $vetture = $this->getRevisioniScadute($dataScadenza, $aDataScadenza, $entityManager);
/*
        dump($bolli);
        dump($assicurazioni);
        dump($patenti);
        dd($vetture);*/

        return $this->render(
            'scadenze/index.html.twig',
            array(
                'bolli' => $bolli,
                'assicurazioni' => $assicurazioni,
                'patenti' => $patenti,
                'vetture' => $vetture,
                'dataScadenza' => $dataScadenza,
                'aDataScadenza' => $aDataScadenza,
                'search_form' => $form->createView()
            ));

        return $this->render('scadenze/index.html.twig', [
            'controller_name' => 'ScadenzeController',
        ]);
    }

    #[Route('/scadenze/{dataScadenza}/{aDataScadenza}', name: 'app_scadenze_index')]
    public function search($dataScadenza, $aDataScadenza, Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(ScadenzaType::class, null, array(
            'action' => $this->generateUrl('app_scadenze_index', array(
                'dataScadenza' => $dataScadenza,
                'aDataScadenza' => $aDataScadenza
            )),
            'method' => 'POST',
            'attr' => array('role' => 'form'),
        ));

        $bolli = $this->getBolliScaduti($dataScadenza, $aDataScadenza, $entityManager);
        $assicurazioni = $this->getAssicurazioniScadute($dataScadenza, $aDataScadenza, $entityManager);
        $patenti = $this->getPatentiScadute($dataScadenza, $aDataScadenza, $entityManager);
        $vetture = $this->getRevisioniScadute($dataScadenza, $aDataScadenza, $entityManager);

       // dump($bolli);

        // dd($assicurazioni);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $dataScadenza = $data['dataScadenza']->format('Y-m-d');

            $aDataScadenza = $data['aDataScadenza']->format('Y-m-d');

            return $form = $this->redirect($this->generateUrl('app_scadenze_index', array(
                'dataScadenza' => $dataScadenza,
                'aDataScadenza' => $aDataScadenza
            )));
        }

        return $this->render(
            'Scadenze/index.html.twig',
            array(
                'bolli' => $bolli,
                'assicurazioni' => $assicurazioni,
                'patenti' => $patenti,
                'vetture' => $vetture,
                'dataScadenza' => $dataScadenza,
                'aDataScadenza' => $aDataScadenza,
                'search_form' => $form->createView()
            ));
    }

    private function getBolliScaduti($dataScadenza, $aDataScadenza, EntityManagerInterface $entityManager)
    {
        $repository = $entityManager->getRepository(Bollo::class);            

        $query = $repository->createQueryBuilder('bollo')
            ->where('bollo.dataScadenzaBollo >= :data')
            ->andWhere('bollo.dataScadenzaBollo <= :dataFine')
            ->setParameters(new ArrayCollection(array(
                new Parameter('data', $dataScadenza),
                new Parameter('dataFine', $aDataScadenza)
            )))
            ->orderBy('bollo.dataScadenzaBollo', 'ASC')
            ->getQuery();

        $bolli = $query->getResult();

        return $bolli;
    }

    private function getAssicurazioniScadute($dataScadenza, $aDataScadenza, EntityManagerInterface $entityManager)
    {
        $repository = $entityManager->getRepository(Assicurazione::class);

        $query = $repository->createQueryBuilder('a')
            ->where('a.dataScadenzaAssicurazione >= :data')
            ->andWhere('a.dataScadenzaAssicurazione <= :dataFine')
            ->setParameters(new ArrayCollection(array(
                new Parameter('data', $dataScadenza),
                new Parameter('dataFine', $aDataScadenza)
            )))
            ->orderBy('a.dataScadenzaAssicurazione', 'ASC')
            ->getQuery();

        $assicurazioni = $query->getResult();

        return $assicurazioni;
    }

    private function getPatentiScadute($dataScadenza, $aDataScadenza, EntityManagerInterface $entityManager)
    {

        $repository = $entityManager->getRepository(Patente::class);

        $query = $repository->createQueryBuilder('p')
            ->where('p.dataScadenzaPatente >= :data')
            ->andWhere('p.dataScadenzaPatente <= :dataFine')
            ->setParameters(new ArrayCollection(array(
                new Parameter('data', $dataScadenza),
                new Parameter('dataFine', $aDataScadenza)
            )))
            ->orderBy('p.dataScadenzaPatente', 'ASC')
            ->getQuery();

        $patenti = $query->getResult();

        return $patenti;
    }

    private function getRevisioniScadute($dataScadenza, $aDataScadenza, EntityManagerInterface $entityManager)
    {
        $repository = $entityManager->getRepository(Vettura::class);

        $query = $repository->createQueryBuilder('v')
            ->where('v.dataScadenzaRevisione >= :data')
            ->andWhere('v.dataScadenzaRevisione <= :dataFine')
            ->setParameters(new ArrayCollection(array(
                new Parameter('data', $dataScadenza),
                new Parameter('dataFine', $aDataScadenza)
            )))
            ->orderBy('v.dataScadenzaRevisione', 'ASC')
            ->getQuery();

        $vetture = $query->getResult();

        return $vetture;
    }
/*
    public function stampaRevisioniAction($dataScadenza, $aDataScadenza)
    {
        $vetture = $this->getRevisioniScadute($dataScadenza, $aDataScadenza);

        $html = $this->renderView(':Stampe:pdfRevisioni.pdf.twig', array(
            "vetture" => $vetture,
            'dataScadenza' => $dataScadenza,
            'aDataScadenza' => $aDataScadenza
        ));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'Revisioni_da_'.$dataScadenza.'_a_'.$aDataScadenza.'.pdf',
            null,
            'inline'
        );
    }

    public function stampaAssicurazioniAction($dataScadenza, $aDataScadenza)
    {
        $assicurazioni = $this->getAssicurazioniScadute($dataScadenza, $aDataScadenza);

        $html = $this->renderView(':Stampe:pdfAssicurazioni.pdf.twig', array(
            "assicurazioni" => $assicurazioni,
            'dataScadenza' => $dataScadenza,
            'aDataScadenza' => $aDataScadenza
        ));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'Assicurazioni_da_'.$dataScadenza.'_a_'.$aDataScadenza.'.pdf',
            null,
            'inline'
        );
    }

    public function stampaBolliAction($dataScadenza, $aDataScadenza)
    {
        $bolli = $this->getBolliScaduti($dataScadenza, $aDataScadenza);

        $html = $this->renderView(':Stampe:pdfBolli.pdf.twig', array(
            'bolli' => $bolli,
            'dataScadenza' => $dataScadenza,
            'aDataScadenza' => $aDataScadenza
        ));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'Bolli_da_'.$dataScadenza.'_a_'.$aDataScadenza.'.pdf',
            null,
            'inline'
        );
    }

    public function stampaPatentiAction($dataScadenza, $aDataScadenza)
    {
        $patenti = $this->getPatentiScadute($dataScadenza, $aDataScadenza);

        $html = $this->renderView(':Stampe:pdfPatenti.pdf.twig', array(
            'patenti' => $patenti,
            'dataScadenza' => $dataScadenza,
            'aDataScadenza' => $aDataScadenza
        ));

        return new PdfResponse(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            'Patenti_da_'.$dataScadenza.'_a_'.$aDataScadenza.'.pdf',
            null,
            'inline'
        );
    }
        */
}
