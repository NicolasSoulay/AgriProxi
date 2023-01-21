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
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

class DevisController extends AbstractController
{

    #[Route('/devis', name: 'app_devis')]
    public function index(): Response
    {
        $produits = [];
        $grouped_produits = [];
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        $type = $entreprise->getTypeEntreprise();
        $devis = $entreprise->getDevis();
        foreach ($devis as $key) {
            foreach ($key->getLigneDevis() as $ligneDevis) {

                $produits[] = $ligneDevis->getProduit();
            }
            $grouped_produits = array_reduce($produits, function ($carry, $item) {
                $carry[$item->getEntreprise()->getId()][] = $item;
                return $carry;
            }, []);
        }
        if (empty($grouped_produits)) {
            return $this->render('devis/index.html.twig', [
                "message" => "Vous n'avez pas encore ajouté de produit à votre liste de devis !"
            ]);
        } else {
            return $this->render('devis/index.html.twig', [
                "produits" => $grouped_produits,
                "devis" => $devis,
                "types" => $type,
                "ligneDevis" => $ligneDevis,
            ]);
        }
    }


    #[Route('/devis/add/{id}', name: 'add_devis')]
    public function addProductDevis($id, ProduitRepository $produitRepo, DevisRepository $devisRepo, LigneDevisRepository $ligneRepo)
    {
        // Je récupère le produit à ajouter au panier
        $product = $produitRepo->find($id);
        $entreprise_id = $product->getEntreprise()->getId();

        // Récupérez ou créez un panier pour l'utilisateur connecté
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        $line = new LigneDevis();
        $line->setProduit($product);
        $line->setUsers($user);
        $line->setEtat(0);
        $line->setEntrepriseId($entreprise_id); //Produit->entreprise->id
        // Vérifie si une ligne de devis existe déjà pour le user en cours avec un même produit->entreprise->id
        $existingLine = $ligneRepo->findOneBy(['users' => $user, 'entrepriseId' => $entreprise_id]);

        // Si une ligne existe déjà, récupérez l'id de devis de cette ligne
        if ($existingLine) {
            $devis = $existingLine->getDevis();
        } else {
            // Sinon, créez un nouveau devis pour l'entreprise en cours
            $devis = new Devis();
            $devis->setEntreprise($entreprise);
            $devisRepo->save($devis, true);
        }

        //je lie ligne devis à devis
        $line->setDevis($devis);
        $ligneRepo->save($line, true);
        return $this->redirectToRoute('app_devis');
    }

    #[Route('/devis/add/{id}', name: 'save_devis1')]
    public function saveDevis(HttpFoundationRequest $request)
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            // Traitement des données
            // ...
        }
        // Je récupère le produit à ajouter au panier
        $product = $produitRepo->find($id);
        $entreprise_id = $product->getEntreprise()->getId();

        // Récupérez ou créez un panier pour l'utilisateur connecté
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();

        $line = new LigneDevis();
        $line->setProduit($product);
        $line->setUsers($user);
        $line->setEtat(0);
        $line->setEntrepriseId($entreprise_id); //Produit->entreprise->id
        // Vérifie si une ligne de devis existe déjà pour le user en cours avec un même produit->entreprise->id
        $existingLine = $ligneRepo->findOneBy(['users' => $user, 'entrepriseId' => $entreprise_id]);

        // Si une ligne existe déjà, récupérez l'id de devis de cette ligne
        if ($existingLine) {
            $devis = $existingLine->getDevis();
        } else {
            // Sinon, créez un nouveau devis pour l'entreprise en cours
            $devis = new Devis();
            $devis->setEntreprise($entreprise);
            $devisRepo->save($devis, true);
        }

        //je lie ligne devis à devis
        $line->setDevis($devis);
        $ligneRepo->save($line, true);
        return $this->redirectToRoute('app_devis');
    }
}
