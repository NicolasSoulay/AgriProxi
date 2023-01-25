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
     * retourne la vue de la page de recherche de produits avec la carte, et donne les coordonnée de la premiere adresse de l'utilisateur. si il n'a pas d'adresse, on donne les coordonnées de la france.
     *
     * @param ProduitRepository $produitRepo
     * @param SousCategorieRepository $subCategorieRepo
     * @param CategorieRepository $categorieRepo
     * @param Request $request
     * @return Response
     */
    #[Route('/produit', name: 'app_produit')]
    public function map(CategorieRepository $categorieRepo): Response
    {
        $userAdresses = $this->getUser()->getEntreprise()->getAdresses();
        if (count($userAdresses) > 0) {
            $latitude = $userAdresses[0]->getLatitude();
            $longitude = $userAdresses[0]->getLongitude();
    
            return $this->render('produit/map.html.twig', [
                'controller_name' => 'ProduitController',
                'categories' => $categorieRepo->findAll(),
                'adresses' => $userAdresses,
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
        } else {
            return $this->redirectToRoute('app_user', [
                'message' => '5'
            ]);
        }
    }


    /**
     * Renvoie un json contenant les infos d'une adresse à partir d'un id d'adresse. 
     * 
     * @param Request $request
     * @param AdresseRepository $adresseRepo
     * @return JsonResponse 
     */
    #[Route('/produit/ajax/adresse/{idAdresse}', name: 'ajax_adresse')]
    public function ajaxAdresse(Request $request, AdresseRepository $adresseRepo): JsonResponse
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
                'status' => 'ça pas marche',
                'message' => "c'est con hein?"
            ),
            400
        );
    }

    /**
     * Renvoie un json contenant l'id et le nom de sous-categories correspondant à un id de categorie 
     * 
     * @param Request $request
     * @param SousCategorieRepository $subCatRepo
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
                'status' => 'ça pas marche',
                'message' => "c'est con hein?"
            ),
            400
        );
    }

    /**
     * Renvoie un json contenant toute les informations accessible dans la base de donnée sur des produit correspondant soit a un id de categorie, soit a un id de sous-categorie.
     * 
     * @param ProduitRepository $produitRepo
     * @param Request $request
     * @return JsonResponse 
     */
    #[Route('/produit/ajax/produitcat/{idCat}/produitsubcat/{idSubCat}', name: 'ajax_produit')]
    public function ajaxProduit(Request $request, ProduitRepository $produitRepo): JsonResponse
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
                'status' => 'ça pas marche',
                'message' => "c'est con hein?"
            ),
            400
        );
    }


    /**
     * renvoi un tableau associatif a partir d'une collection de produits
     *
     * @param Produit[] $produits
     * @return array
     */
    public function templateJsonProduit(array $produits): array
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
     * Retourne un tableau contenant les coordonée, le nom, l'adresse et l'Id d'une entreprise, sans doublon d'entreprise, a partir d'une collection de produits
     * 
     * @param Produit[] $produits
     * @return array[] 
     */
    public function getCoordinatesEntreprises(array $produits): array
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
     * Retourne un tableau de produits, filtrés par categories ou sous-categories
     *
     * @param string $subCatId
     * @param string $catId
     * @param ProduitRepository $produitRepo
     * @return Produit[] 
     */
    public function searchByCategorieOrSubCategorie(string $subCatId, string $catId, ProduitRepository $produitRepo): array
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
                    $message = 'Le produit a bien été créé';
                    break;
                case '1':
                    $message = 'Le produit a bien été modifié';
                    break;
                case '2':
                    $message = 'Le produit a bien été supprimé';
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

    // Affichage Formulaire pour l'entité Produit 
    public function formProduit(Produit $produit, ProduitRepository $produitRepo, Request $request, #[Autowire('%photo_dir%')] string $photoDir)
    {
        $message = '';
        $form = $this->createForm(ProduitCreationFormType::class, $produit);
        $form->handleRequest($request);
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        if ($form->isSubmitted() && $form->isValid()) {
            //En cas de modification vérifie si une image existe et la supprime
            if(isset($_POST['produit_creation_form[imageURL]']) && $_POST['produit_creation_form[imageURL]'] !== ''){
                $this->deleteImage($produit, $photoDir);
            }
            //Gestion de l'image pour la créer dans un dossier
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
            //Gestion des messages de validation
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
    public function deleteProduit(Produit $produit, ProduitRepository $produitRepo, #[Autowire('%photo_dir%')] string $photoDir): Response
    {
        //Check si l'image existe et la supprime
        $this->deleteImage($produit, $photoDir);
        $produitRepo->remove($produit, true);
        return $this->redirectToRoute('maBoutique', [
            'message' => '2',
        ]);
    }

    /**
     * Fonction qui vérifie si une image existe sur le produit et la supprime
     */
    private function deleteImage(Produit $produit, #[Autowire('%photo_dir%')] string $photoDir)
    {
        $imageUrl = $produit->getImageURL();
        if ($imageUrl !== null || $imageUrl === '') {
            $imageUrl = explode('/', $imageUrl);
            $image = $imageUrl[count($imageUrl) - 1];
            unlink($photoDir . '/' . $image);
        }
    }
}
