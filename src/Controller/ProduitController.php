<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitCreationFormType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\SousCategorieRepository;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{

    /**
     *  retourne la vue de la page de recherche de produits avec la carte
     */
    #[Route('/produit', name: 'app_produit')]
    public function index(ProduitRepository $produitRepo, SousCategorieRepository $subCategorieRepo, CategorieRepository $categorieRepo, Request $request): Response
    {
        $subCategorie = $request->get("subCategorie", '');
        $categorie = $request->get("categorie", '');
        $userAdresses = $this->getUser()->getEntreprise()->getAdresses();
        $latitude = $this->getLatitudeFromUrlOrUser($userAdresses);
        $longitude = $this->getLongitudeFromUrlOrUser($userAdresses);
        $zoomLevel = $this->getRadius($request->get("rayon", ''));
        $produits = $this->searchByCategorieOrSubCategorie($subCategorie, $categorie, $produitRepo);
        $coordinatesProduits = $this->getCoordinatesProduits($produits);

        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
            'produits' => $produits,
            'subCategories' => $subCategorieRepo->findAll(),
            'categories' => $categorieRepo->findAll(),
            'adresses' => $userAdresses,
            'coordinatesProduits' => $coordinatesProduits,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoomLevel' => $zoomLevel,
        ]);
    }


    /**
     * @return array[] Retourne un tableau de coordonnées, filtrés par produits
     */
    public function getCoordinatesProduits($produits)
    {
        $coordinates = [];
        foreach ($produits as $produit) {
            $entreprise = $produit->getEntreprise();
            $adresses = $entreprise->getAdresses();
            $entrepriseName = $entreprise->getName();
            $entrepriseId = $entreprise->getId();
            foreach ($adresses as $adresse) {
                $concat = $adresse->getLatitude() . "," . $adresse->getLongitude() . "," . $entrepriseName . "," . $entrepriseId;
                if (!in_array($concat, $coordinates) && $concat != "0,0") {
                    $coordinates[] = $concat;
                }
            }
        }
        return $coordinates;
    }

    /**
     * @return int retourne la latitude en décimal à partir d'une entité "Adresse"
     */
    public function getLatitudeFromUrlOrUser($adresses)
    {
        if (!isset($_GET['adresse']) || $_GET['adresse'] === '') {
            return $adresses[0]->getLatitude();
        }
        foreach ($adresses as $adresse) {
            if ($adresse->getId() == $_GET['adresse']) {
                return $adresse->getLatitude();
            }
        }
    }


    /**
     * @return int retourne la longitude en décimal à partir d'une entité "Adresse"
     */
    public function getLongitudeFromUrlOrUser($adresses)
    {
        if (!isset($_GET['adresse']) || $_GET['adresse'] === '') {
            return $adresses[0]->getLongitude();
        }
        foreach ($adresses as $adresse) {
            if ($adresse->getId() == $_GET['adresse']) {
                return $adresse->getLongitude();
            }
        }
    }

    /**
     * @return int retourne la valeur de zoom en décimale pour leaflet a partir du choix de rayon de recherche
     */
    //zoom level: 
    //12 = 30km -> r = 10km 
    //11 = 75km -> r = 25km 
    //10 = 150km -> r = 50km
    //9 = 240km -> r = 100km
    public function getRadius($rayon)
    {
        switch ($rayon) {
            case '0':
                return 6;
                break;
            case '10':
                return 12;
                break;
            case '25':
                return 11;
                break;
            case '50':
                return 10;
                break;
            case '100':
                return 9;
                break;
            default:
                return 10;
        }
    }

    /**
     * @return Produit[] Retourne un tableau de produits, filtrés par categories ou sous-categories
     */
    public function searchByCategorieOrSubCategorie($subCat, $cat, $produitRepo)
    {
        if (!isset($_GET['subCategorie']) || $_GET['subCategorie'] === '') {
            return $produitRepo->findByCategorie($cat);
        }
        return $produitRepo->findBySubCategorie($subCat);
    }

    //Affiche la page de ma boutique
    #[Route('/ma_boutique', name: 'maBoutique')]
    public function maBoutique(Request $request): Response
    {
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        $produits = $entreprise->getProduits();
        $message = '';
        if(isset($_GET['message'])){
            switch ($_GET['message']){
                case '0':
                    $message = 'Le produit a bien été créé';
                    break;
                case '1' :
                    $message = 'Le produit a bien été modifié';
                    break;
                case '2' :
                    $message = 'Le produit a bien été supprimé';
                    break;
                default :
                    $message = '';
            }
        }

        return $this->render('produit/ma_boutique.html.twig', [
            'produits' => $produits,
            'entreprise' => $entreprise,
            'message' => $message
        ]);
    }

    // Affichage Formulaire pour l'entité Produit 
    public function formProduit(Produit $produit, ProduitRepository $produitRepo, Request $request, #[Autowire('%photo_dir%')] string $photoDir)
    {
        $message = '';
        $form = $this->createForm(ProduitCreationFormType::class, $produit);
        $form->handleRequest($request);
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        if ($form->isSubmitted() && $form->isValid()) {
            if ($photo = $form['imageURL']->getData()) {
                $filename = '/uploads/photos/' . bin2hex(random_bytes(6)) . '.' . $photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                }
                $produit->setImageURL($filename);
            }
            $produit->setEntreprise($entreprise);
            $produitRepo->save($produit, true);
            if ($request->get('id')) {
                $message = '1';
            } else {
                $message = '0';
            }
        return $this->redirectToRoute('maBoutique',[
            'message' => $message,
        ]);
        } elseif ($form->isSubmitted()) {
            $message = 'Les informations ne sont pas valides';
        }
        return $this->render('produit/create_produit.html.twig', [
            'form_produit' => $form->createView(),
            'message' => $message
        ]);
    }

    //Page de création de produit
    #[Route('/create_produit', name: 'createProduit')]
    public function createProduit(ProduitRepository $produitRepo, Request $request, #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        $produit = new Produit();
        return $this->formProduit($produit, $produitRepo, $request, $photoDir);
    }

    //Page de mise à jour de produit
    #[Route('/create_produit/{id}', name: 'updateProduit')]
    public function updateProduit(Produit $produit, ProduitRepository $produitRepo, Request $request, #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        return $this->formProduit($produit, $produitRepo, $request, $photoDir);
    }

    //Suppression d'un produit
    #[Route('/delete_produit/{id}', name: 'deleteProduit')]
    public function deleteProduit(Produit $produit, ProduitRepository $produitRepo): Response
    {
        $produitRepo->remove($produit, true);
        return $this->redirectToRoute('maBoutique',[
            'message' => '2',
        ]);
    }
}
