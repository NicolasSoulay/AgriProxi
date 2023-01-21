<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitCreationFormType;
use App\Form\TestFormType;
use App\Repository\CategorieRepository;
use App\Repository\ProduitRepository;
use App\Repository\SousCategorieRepository;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ProduitController extends AbstractController
{

    /**
     * retourne la vue de la page de recherche de produits avec la carte
     *
     * @param ProduitRepository $produitRepo
     * @param SousCategorieRepository $subCategorieRepo
     * @param CategorieRepository $categorieRepo
     * @param Request $request
     * @return Response
     */
    #[Route('/produit', name: 'app_produit')]
    public function map(ProduitRepository $produitRepo, SousCategorieRepository $subCategorieRepo, CategorieRepository $categorieRepo, Request $request): Response
    {
        $subCategorie = $request->get("subCategorie", '');
        $categorie = $request->get("categorie", '');
        $userAdresses = $this->getUser()->getEntreprise()->getAdresses();
        $latitude = $this->getLatitudeFromUrlOrUser($userAdresses);
        $longitude = $this->getLongitudeFromUrlOrUser($userAdresses);
        $zoomLevel = $this->getRadius($request->get("rayon", ''));
        $produits = $this->searchByCategorieOrSubCategorie($subCategorie, $categorie, $produitRepo);
        $coordinatesProduits = $this->getCoordinatesProduits($produits);

        return $this->render('produit/map.html.twig', [
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
     * Renvoie un tableau:  
     * idCategorie => [[idSubCat1, nomSubCat1],..] 
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/produit/ajax/subcat/{id}', name: 'ajax_subcat')]
    public function ajaxSubCat(Request $request, SousCategorieRepository $subCatRepo)
    {
        if (isset($request->request)) {
            $idCategorie = $request->get('id');
            $subcats = $subCatRepo->findByCategorie($idCategorie);
            $json = [];
            $i = 0;
            foreach ($subcats as $subcat) {
                $json[$i][] = $subcat->getId();
                $json[$i][] = $subcat->getName();
                $i++;
            }
            return new JsonResponse($json, 200);
        }

        return new JsonResponse(
            array(
                'status' => 'ça pas marche',
                'message' => "c'est con hein?"
            ),
            400
        );
    }

    /**
     * Renvoie un tableau:  
     * idCategorie |idSousCategorie => [[idProduit1, nomProduit1, descriptionProduit1, imageProduit1, idEntrepriseProduit1, nomEntrepriseProduit1, idAdresseEntrepriseProduit1, labelAdresseEntrepriseProduit1, codePostalAdresseEntrepriseProduit1, villeAdresseEntrepriseProduit1 latitudeEntrepriseProduit1, longitudeEntrepriseProduit1],..]
     * 
     * @param Request $request
     * @return ?
     */
    #[Route('/produit/ajax/produit/{id}', name: 'ajax_produit')]
    public function ajaxProduit(Request $request)
    {
    }

    /**
     * Renvoie un tableau: 
     * idEntrepriseUser => [[idAdresseUser1, labelAdresseUser1, codePostalAdresseUser1, villeAdresseUser1, latitudeAdresseUser1, LongitudeAdresseUser1],..]
     * 
     * @param Request $request
     * @return ?
     */
    #[Route('/produit/ajax/user/{id}', name: 'ajax_user')]
    public function ajaxAdresseUser(Request $request)
    {
    }


    /**
     * Retourne un tableau contenant les coordonée, le nom et l'Id d'une entreprise, filtrés par produits
     * 
     * @param Produit[] $produits
     * @return array[] 
     */
    public function getCoordinatesProduits(array $produits)
    {
        $coordinates = [];
        foreach ($produits as $produit) {
            $entreprise = $produit->getEntreprise();
            $adresses = $entreprise->getAdresses();
            $entrepriseName = $entreprise->getName();
            $entrepriseId = $entreprise->getId();
            foreach ($adresses as $adresse) {
                $lat = $adresse->getLatitude();
                $long = $adresse->getLongitude();
                $adresseCoordinate = $lat . "," . $long;
                if ($adresseCoordinate != "0,0") {
                    $concat = $adresseCoordinate . "," . $entrepriseName . "," . $entrepriseId;
                    if (!in_array($concat, $coordinates)) {
                        $coordinates[] = $concat;
                    }
                }
            }
        }
        return $coordinates;
    }

    /**
     * retourne la latitude en décimal de l'adresse pointé par l'Url, sinon de la premiere adresse de l'utilisateur
     * 
     * @param Adresse|Adresse[] $adresses
     * @return int 
     */
    public function getLatitudeFromUrlOrUser(mixed  $adresses)
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
     * retourne la longitude en décimal de l'adresse pointé par l'Url, sinon de la premiere adresse de l'utilisateur
     * 
     * @param Adresse|Adresse[] $adresses
     * @return int 
     */
    public function getLongitudeFromUrlOrUser(mixed $adresses)
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
     * retourne la valeur de zoom en décimale pour leaflet a partir du choix de rayon de recherche
     * 
     * @param string
     * @return int 
     */
    public function getRadius(int $rayon)
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
                return 7;
        }
    }

    /**
     * Retourne un tableau de produits, filtrés par categories ou sous-categories
     *
     * @param string $subCatId
     * @param string $catId
     * @param ProduitRepository $produitRepo
     * @return Produit[] 
     */
    public function searchByCategorieOrSubCategorie(string $subCatId, string $catId, ProduitRepository $produitRepo)
    {
        if (!isset($_GET['subCategorie']) || $_GET['subCategorie'] === '') {
            return $produitRepo->findByCategorie($catId);
        }
        return $produitRepo->findBySubCategorie($subCatId);
    }

    //Affiche la page de ma boutique
    #[Route('/ma_boutique', name: 'maBoutique')]
    public function maBoutique(): Response
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
