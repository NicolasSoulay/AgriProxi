<?php

namespace App\Controller;

use App\Entity\Devis;
use App\Entity\LigneDevis;
use App\Repository\DevisRepository;
use App\Repository\LigneDevisRepository;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityRepository;

class DevisController extends AbstractController
{
    #[Route('/devis', name: 'app_devis')]
    public function index(LigneDevisRepository $ligneRepo): Response
    {
        $produits = [];
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        $devis = $entreprise->getDevis();
        foreach ($devis as $key) {
            foreach ($key->getLigneDevis() as $ligneDevis) {

                $produits[] = $ligneDevis->getProduit();
            }
            // La fonction array_reduce() va parcourir votre tableau de produits et pour chaque produit, elle va vérifier son id entreprise. Si l'id entreprise existe déjà dans le tableau $carry, elle va ajouter le produit dans ce tableau associatif, sinon elle va créer une nouvelle entrée dans ce tableau associatif avec l'id entreprise en clé.
            $grouped_produits = array_reduce($produits, function ($carry, $item) {
                $carry[$item->getEntreprise()->getId()][] = $item;
                return $carry;
            }, []);
            return $this->render('devis/index.html.twig', [
                "produits" => $grouped_produits,
                // "entreprises" => $entreprise
            ]);
        }
    }
    #[Route('/devis/add/{id}', name: 'add_devis')]
    public function addProductDevis($id, ProduitRepository $produitRepo, DevisRepository $devisRepo, LigneDevisRepository $ligneRepo)
    {
        // Je recupère le produit à ajouter au panier
        $product = $produitRepo->find($id);
        // Récupérez ou créez un panier pour l'utilisateur connecté
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        $devis =  $devisRepo->findOneBy(['entreprise' => $entreprise]);
        if (!$devis) {
            $devis = new Devis();
            $devis->setEntreprise($entreprise);
        }
        //je lie ligne devis à devis
        $line = new LigneDevis();
        $line->setProduit($product);
        $line->setEtat(0);
        $line->setDevis($devis);

        $devisRepo->save($devis, true);
        $ligneRepo->save($line, true);
        return $this->redirectToRoute('app_devis');
    }
}
