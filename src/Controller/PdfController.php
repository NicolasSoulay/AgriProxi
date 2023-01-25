<?php

namespace App\Controller;

use App\Repository\LigneDevisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;

class PdfController extends AbstractController
{

    #[Route('/pdf/{id}', name: 'pdf_generator')]
    public function generatePdf($id, LigneDevisRepository $ligneRepo)
    {
        $data = $ligneRepo->find($id);

        //Données de l'acheteur
        $usersA = $data->getUsers();
        $entrepriseA = $usersA->getEntreprise();
        $adresseA = $entrepriseA->getAdresses();

        //Données du producteur 
        $userP = $data->getProduit();
        $entrepriseP = $userP->getEntreprise();
        $adresseP = $entrepriseP->getAdresses();

        $html = $this->renderView('pdf/template.html.twig', [
            'data' => $data,
            'adresseA' => $adresseA,
            'adresseP' => $adresseP,
        ]);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
        $dompdf->stream("Devis", [
            "Attachment" => true,
        ]);

        return new Response();
    }
}
