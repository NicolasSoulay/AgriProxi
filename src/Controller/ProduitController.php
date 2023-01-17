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
        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
            'produits' => $produitRepo->findBySubCategorie($subCategorie),
            'subCategories' => $subCategorieRepo->findAll(),
            'categories' => $categorieRepo->findAll(),
        ]);
    }
}
