<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitCreationFormType;
use App\Repository\AdresseRepository;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\SousCategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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

        return $this->render('produit/index.html.twig', [
            'controller_name' => 'ProduitController',
            'produits' => $produitRepo->findBySubCategorie($subCategorie),
            'subCategories' => $subCategorieRepo->findAll(),
            'categories' => $categorieRepo->findAll(),
            'adresses' => $userAdresses,
            'latitude' => $latitude,
            'longitude' => $longitude,
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

    //Affiche la page de ma boutique
    #[Route('/ma_boutique', name: 'maBoutique')]
    public function maBoutique(): Response
    {   
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        $produits = $entreprise->getProduits();
        return $this->render('produit/ma_boutique.html.twig', [
            'produits' => $produits,
            'entreprise' => $entreprise
        ]);
    }

    //Page de création de produit
    #[Route('/create_produit', name: 'createProduit')]
    public function createProduit(ProduitRepository $produitRepo, Request $request, #[Autowire('%photo_dir%')] string $photoDir): Response
    {   
        $produit = new Produit();
        $message = '';
        $form = $this->createForm(ProduitCreationFormType::class, $produit);
        $form->handleRequest($request);
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        if($form->isSubmitted() && $form->isValid()){
            var_dump($form['photo']);
            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // unable to upload the photo, give up
                }
                $produit->setImageURL($filename);
                }
            $produit->setEntreprise($entreprise);
            $produitRepo->save($produit, true);
            if($request->get('id')){
                $message = 'Le produit a bien été modifié';
            }else{
                $message = 'Le produit a bien été créé';
            }
        }
        elseif($form->isSubmitted()){
            $message = 'Les informations ne sont pas valides';
        }
        return $this->render('produit/create_produit.html.twig', [
            'form_produit' => $form->createView(),
            'message' => $message
        ]);
    }
}
