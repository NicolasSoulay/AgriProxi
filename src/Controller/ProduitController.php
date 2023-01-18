<?php

namespace App\Controller;

use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\SousCategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{
    #[Route('/produit', name: 'app_produit')]
    public function index(ProduitRepository $produitRepo, SousCategorieRepository $subCategorieRepo, CategorieRepository $categorieRepo, Request $request): Response
    {
        $subCategorie = $request->get("subCategorie", '');
        $categorie = $request->get("categorie", '');
        $userAdresses = $this->getUser()->getEntreprise()->getAdresses();
        $latitude = $this->getLatitudeFromUrlOrUser($userAdresses);
        $longitude = $this->getLongitudeFromUrlOrUser($userAdresses);
        $zoomLevel = $this->getRadius($request->get("rayon", ''));

        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
            'produits' => $this->searchByCategorieOrSubCategorie($subCategorie, $categorie, $produitRepo),
            'subCategories' => $subCategorieRepo->findAll(),
            'categories' => $categorieRepo->findAll(),
            'adresses' => $userAdresses,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoomLevel' => $zoomLevel,
        ]);
    }

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

    public function getRadius($rayon)
    {
        switch ($rayon) {
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

    public function searchByCategorieOrSubCategorie($subCat, $cat, $produitRepo)
    {
        if (!isset($_GET['subCategorie']) || $_GET['subCategorie'] === '') {
            return $produitRepo->findByCategorie($cat);
        }
        return $produitRepo->findBySubCategorie($subCat);
    }
}
