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
        $produit = $data->getProduit();


        $html = $this->renderView('pdf/template.html.twig', [
            'data' => $data,
            'produit' => $produit
        ]);

        // instantiation de dompdf
        $dompdf = new Dompdf();
        // chargement de la vue
        $dompdf->loadHtml($html);

        // (Optionnel) Mise en page du PDF
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("mypdf.pdf", [
            "Attachment" => true
        ]);

        return new Response();
    }
}
