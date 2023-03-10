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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    public function map(CategorieRepository $categorieRepo, Request $request): Response
    {
        $userAdresses = $this->getUser()->getEntreprise()->getAdresses();
        $latitude = $userAdresses[0]->getLatitude();
        $longitude = $userAdresses[0]->getLongitude();
        $zoomLevel = $this->getRadius($request->get("rayon", ''));

        return $this->render('produit/map.html.twig', [
            'controller_name' => 'ProduitController',
            'categories' => $categorieRepo->findAll(),
            'adresses' => $userAdresses,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'zoomLevel' => $zoomLevel,
        ]);
    }


    /**
     * Renvoie un tableau:  
     * idCategorie => [[idSubCat1, nomSubCat1],[..],..] 
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/produit/ajax/adresse/{idAdresse}', name: 'ajax_adresse')]
    public function ajaxAdresse(Request $request, AdresseRepository $adresseRepo)
    {
        if (isset($request->request)) {
            $idAdresse = $request->get('idAdresse');
            $adresse = $adresseRepo->findBy(['id' => $idAdresse]);
            $json[] = [
                "id" => $adresse[0]->getId(),
                "label" => $adresse[0]->getLabel(),
                "complement" => $adresse[0]->getComplement(),
                "zipCode" => $adresse[0]->getZipCode(),
                "latitude" => $adresse[0]->getLatitude(),
                "longitude" => $adresse[0]->getLongitude(),
                "ville" => $adresse[0]->getVille(),
            ];

            return new JsonResponse($json, 200);
        }

        return new JsonResponse(
            array(
                'status' => '??a pas marche',
                'message' => "c'est con hein?"
            ),
            400
        );
    }

    /**
     * Renvoie un tableau:  
     * idCategorie => [[idSubCat1, nomSubCat1],[..],..] 
     * 
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/produit/ajax/subcat/{idCat}', name: 'ajax_subcat')]
    public function ajaxSubCat(Request $request, SousCategorieRepository $subCatRepo)
    {
        if (isset($request->request)) {
            $idCategorie = $request->get('idCat');
            $idCategorie = $request->get('idCat');
            $subcats = $subCatRepo->findByCategorie($idCategorie);
            $json = [];
            foreach ($subcats as $subcat) {
                $json[] = [
                    "id" => $subcat->getId(),
                    "name" => $subcat->getName()
                ];
            }
            return new JsonResponse($json, 200);
        }

        return new JsonResponse(
            array(
                'status' => '??a pas marche',
                'message' => "c'est con hein?"
            ),
            400
        );
    }

    /**
     * Renvoie un tableau:  
     * idCategorie |idSousCategorie => [[idProduit1, nomProduit1, descriptionProduit1, inStockProduit1, imageProduit1, idEntrepriseProduit1, nomEntrepriseProduit1, idAdresseEntrepriseProduit1, labelAdresseEntrepriseProduit1, codePostalAdresseEntrepriseProduit1, latitudeEntrepriseProduit1, longitudeEntrepriseProduit1, IdVilleAdresseEntrepriseProduit1, nomVilleAdresseEntrepriseProduit1, idDepartementVilleAdresseEntrepriseProduit1, codeDepartementVilleAdresseEntrepriseProduit1, codeDepartementVilleAdresseEntrepriseProduit1],[..],..]
     * idCategorie |idSousCategorie => [[idProduit1, nomProduit1, descriptionProduit1, inStockProduit1, imageProduit1, idEntrepriseProduit1, nomEntrepriseProduit1, idAdresseEntrepriseProduit1, labelAdresseEntrepriseProduit1, codePostalAdresseEntrepriseProduit1, latitudeEntrepriseProduit1, longitudeEntrepriseProduit1, IdVilleAdresseEntrepriseProduit1, nomVilleAdresseEntrepriseProduit1, idDepartementVilleAdresseEntrepriseProduit1, codeDepartementVilleAdresseEntrepriseProduit1, codeDepartementVilleAdresseEntrepriseProduit1],[..],..]
     * 
     * @param Request $request
     * @return ?
     */
    #[Route('/produit/ajax/produitcat/{idCat}/produitsubcat/{idSubCat}', name: 'ajax_produit')]
    public function ajaxProduit(Request $request, ProduitRepository $produitRepo)
    {
        if (isset($request->request)) {
            $idCat = $request->get('idCat');
            $idSubCat = $request->get('idSubCat');
            if ($idSubCat !== 'a' && $idCat !== 'a') {
                $produits = $produitRepo->findBySubCategorie($idSubCat);
                $json = [
                    "produits" => $this->templateJsonProduit($produits),
                    "entreprises" => $this->getCoordinatesEntreprises($produits)
                ];
                return new JsonResponse($json, 200);
            }
            if ($idSubCat === 'a' && $idCat !== 'a') {
                $produits = $produitRepo->findByCategorie($idCat);
                $json = [
                    "produits" => $this->templateJsonProduit($produits),
                    "entreprises" => $this->getCoordinatesEntreprises($produits)
                ];
                return new JsonResponse($json, 200);
            }
            $json = ["salut ya rien"];
            return new JsonResponse($json, 200);
        }
        return new JsonResponse(
            array(
                'status' => '??a pas marche',
                'message' => "c'est con hein?"
            ),
            400
        );
    }

    public function templateJsonProduit($produits)
    {
        $json = [];
        foreach ($produits as $produit) {
            $adresses = $produit->getEntreprise()->getAdresses();
            $adressesEntreprise = [];
            foreach ($adresses as $adresse) {
                $adressesEntreprise = [
                    "id" => $adresse->getId(),
                    "label" => $adresse->getLabel(),
                    "complement" => $adresse->getComplement(),
                    "zipCode" => $adresse->getZipCode(),
                    "latitude" => $adresse->getLatitude(),
                    "longitude" => $adresse->getLongitude(),
                    "ville" => [
                        "id" => $adresse->getVille()->getId(),
                        "name" => $adresse->getVille()->getName(),
                        "departement" => [
                            "id" => $adresse->getVille()->getDepartement()->getId(),
                            "code" => $adresse->getVille()->getDepartement()->getCode(),
                            "name" => $adresse->getVille()->getDepartement()->getName()
                        ]
                    ]
                ];
            }
            $json[] = [
                "id" => $produit->getId(),
                "name" => $produit->getName(),
                "desc" => $produit->getDescription(),
                "inStock" => $produit->isInStock(),
                "imageURL" => $produit->getImageURL(),
                "entreprise" => [
                    "id" => $produit->getEntreprise()->getId(),
                    "name" => $produit->getEntreprise()->getName(),
                    "adresses" => $adressesEntreprise

                ]
            ];
        }
        return $json;
    }


    /**
     * Retourne un tableau contenant les coordon??e, le nom, l'adresse et l'Id d'une entreprise, filtr??s par produits
     * 
     * @param Produit[] $produits
     * @return array[] 
     */
    public function getCoordinatesEntreprises(array $produits)
    {
        $entreprises = [];
        foreach ($produits as $produit) {
            $entreprise = $produit->getEntreprise();
            $adresses = $entreprise->getAdresses();
            $entrepriseName = $entreprise->getName();
            $entrepriseId = $entreprise->getId();
            foreach ($adresses as $adresse) {
                $lat = $adresse->getLatitude();
                $long = $adresse->getLongitude();
                $adresseEntreprise = [
                    "id" => $adresse->getId(),
                    "label" => $adresse->getLabel(),
                    "complement" => $adresse->getComplement(),
                    "zipCode" => $adresse->getZipCode(),
                    "ville" => [
                        "id" => $adresse->getVille()->getId(),
                        "name" => $adresse->getVille()->getName(),
                        "departement" => [
                            "id" => $adresse->getVille()->getDepartement()->getId(),
                            "code" => $adresse->getVille()->getDepartement()->getCode(),
                            "name" => $adresse->getVille()->getDepartement()->getName()
                        ]
                    ]
                ];
                $adresseCoordinate = [
                    "latitude" => $lat,
                    "longitude" => $long,
                ];
                if ($adresseCoordinate["latitude"] != "0" && $adresseCoordinate["longitude"] != "0") {
                    $entreprise = [
                        "id" => $entrepriseId,
                        "name" => $entrepriseName,
                        "adresse" => $adresseEntreprise,
                        "coordinates" => $adresseCoordinate
                    ];
                    if (!in_array($entreprise, $entreprises)) {
                        $entreprises[] = $entreprise;
                    }
                }
            }
        }
        return $entreprises;
    }

    /**
     * retourne la valeur de zoom en d??cimale pour leaflet a partir du choix de rayon de recherche
     * 
     * @param string
     * @return int 
     */
    public function getRadius(string $rayon)
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
     * Retourne un tableau de produits, filtr??s par categories ou sous-categories
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
        if (isset($_GET['message'])) {
            switch ($_GET['message']) {
                case '0':
                    $message = 'Le produit a bien ??t?? cr????';
                    break;
                case '1':
                    $message = 'Le produit a bien ??t?? modifi??';
                    break;
                case '2':
                    $message = 'Le produit a bien ??t?? supprim??';
                    break;
                default:
                    $message = '';
            }
        }

        return $this->render('produit/ma_boutique.html.twig', [
            'produits' => $produits,
            'entreprise' => $entreprise,
            'message' => $message
        ]);
    }

    // Affichage Formulaire pour l'entit?? Produit 
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
            return $this->redirectToRoute('maBoutique', [
                'message' => $message,
            ]);
            return $this->redirectToRoute('maBoutique', [
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

    //Page de cr??ation de produit
    #[Route('/create_produit', name: 'createProduit')]
    public function createProduit(ProduitRepository $produitRepo, Request $request, #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        $produit = new Produit();
        return $this->formProduit($produit, $produitRepo, $request, $photoDir);
    }

    //Page de mise ?? jour de produit
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
        return $this->redirectToRoute('maBoutique', [
            'message' => '2',
        ]);
    }
}
