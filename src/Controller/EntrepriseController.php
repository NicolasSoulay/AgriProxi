<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Entreprise;
use App\Entity\User;
use App\Form\EntrepriseCreationFormType;
use App\Form\EntrepriseUpdateFormType;
use App\Repository\AdresseRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\UserRepository;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseController extends AbstractController
{
    //Affichage fiche entreprise 
    #[Route('/entreprise/{id}', name: 'viewEntreprise')]
    public function show(string $id, EntrepriseRepository $entrepriseRepo): Response
    {
        $entreprise = $entrepriseRepo->find($id);
        $adresses = $entreprise->getAdresses();
        $produits = $entreprise->getProduits();
        return $this->render('entreprise/show.html.twig', [
            'entreprise' => $entreprise,
            'adresses' => $adresses,
            'produits' => $produits
        ]);
    }


    //Affichage Formulaire pour l'entité Entreprise
    private function formEntreprise(Entreprise $entreprise, EntrepriseRepository $entrepriseRepo, UserRepository $userRepo, Request $request, User $user)
    {
        $message = '';
        $form = $this->createForm(EntrepriseCreationFormType::class, $entreprise);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entrepriseRepo->save($entreprise, true);
            $user->setEntreprise($entreprise);
            $user->setRoles(["ROLE_USER", "ROLE_CLIENT"]);
            if ($entreprise->getTypeEntreprise()->getId() === 1) {
                $user->setRoles(["ROLE_USER", "ROLE_PRODUCTEUR"]);
            }
            $userRepo->save($user, true);
            return $this->redirectToRoute('app_login');
        } elseif ($form->isSubmitted()) {
            $message = 'Les informations ne sont pas valides, ou cette entreprise existe déjà';
        }

        return $this->render('entreprise/create_entreprise.html.twig', [
            'form_entreprise' => $form->createView(),
            'message' => $message,
        ]);
    }

    //Page de création d'entreprise
    #[Route('/create_entreprise', name: 'createEntreprise')]
    public function createEntreprise(EntrepriseRepository $entrepriseRepo, UserRepository $userRepo, Request $request): Response
    {
        $userId = $userRepo->getLastId();
        $user = $userRepo->find($userId);
        $entreprise = new Entreprise();
        return $this->formEntreprise($entreprise, $entrepriseRepo, $userRepo, $request, $user);
    }

    //Page de modification de l'entreprise
    #[Route('/update_entreprise/{id}', name: 'updateEntreprise')]
    public function updateEntreprise(Entreprise $entreprise, EntrepriseRepository $entrepriseRepo, UserRepository $userRepo, Request $request): Response
    {
        $user = $this->getUser();
        $message = '';
        $form = $this->createForm(EntrepriseUpdateFormType::class, $entreprise);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entrepriseRepo->save($entreprise, true);
            $user->setEntreprise($entreprise);
            $userRepo->save($user, true);
            $message = 'L\'entreprise a bien été modifiée';
            return $this->redirectToRoute('app_user', [
                'message' => '0'
            ]);
        } elseif ($form->isSubmitted()) {
            $message = 'Les informations ne sont pas valides, ou cette entreprise existe déjà';
        }

        return $this->render('entreprise/create_entreprise.html.twig', [
            'form_entreprise' => $form->createView(),
            'message' => $message,
        ]);
    }


    //Affichage Formulaire pour l'entité Adresse
    private function formAdresse(Adresse $adresse, AdresseRepository $adresseRepo, Request $request, Entreprise $entreprise, VilleRepository $villeRepo)
    {
        $message = '';
        if (isset($_POST['submitAdresse'])) {
            $adresse->setLabel($_POST['label']);
            $adresse->setComplement($_POST['complement']);
            $adresse->setZipCode($_POST['zip_code']);
            $adresse->setEntreprise($entreprise);
            $ville = $villeRepo->find($_POST['villeId']);
            $adresse->setLongitude($_POST['longitude']);
            $adresse->setLatitude($_POST['latitude']);
            $adresse->setVille($ville);
            $adresseRepo->save($adresse, true);
            if ($request->get('id')) {
                $message = 'L\'adresse a bien été modifiée';
                return $this->redirectToRoute('app_user', [
                    'message' => '2'
                ]);
            } else {
                $message = 'L\'adresse a bien été créée';
                if ($this->getUser()) {
                    return $this->redirectToRoute('app_user', [
                        'message' => '1'
                    ]);
                } else {
                    return $this->redirectToRoute('app_login');
                }
            }
        }
        return $this->render('entreprise/create_adresse.html.twig', [
            'message' => $message,
        ]);
    }

    //Page de création d'adresse
    #[Route('/create_adresse', name: 'createAdresse')]
    public function createAdresse(AdresseRepository $adresseRepo, Request $request, EntrepriseRepository $entrepriseRepo, VilleRepository $villeRepo): Response
    {
        $entreprise = $this->getUser()->getEntreprise();
        $adresse = new Adresse();
        return $this->formAdresse($adresse, $adresseRepo, $request, $entreprise, $villeRepo);
    }

    //Page de modification d'adresse
    #[Route('/update_adresse/{id}', name: 'updateAdresse')]
    public function updateAdresse(Adresse $adresse, AdresseRepository $adresseRepo, Request $request, VilleRepository $villeRepo): Response
    {
        $entreprise = $this->getUser()->getEntreprise();
        return $this->formAdresse($adresse, $adresseRepo, $request, $entreprise, $villeRepo);
    }

    //Suppression adresse
    #[Route('/delete_adresse/{id}', name: 'deleteAdresse')]
    public function deleteAdresse(Adresse $adresse, AdresseRepository $adresseRepo): Response
    {
        $adresseRepo->remove($adresse, true);
        return $this->redirectToRoute('app_user', [
            'message' => '3'
        ]);
    }


    /**
     * renvoie un fichier Json contenant l'id et le nom d'entreprise ayant un nom similaire à une string
     * 
     * @param EntrepriseRepository $entrepriseRepo
     * @param Request $request
     * @return JsonResponse $json
     */
    #[Route('/entreprise/ajax/{name}', name: 'entrepriseAjax')]
    public function ajaxEntrepriseByName(Request $request, EntrepriseRepository $entrepriseRepo): JsonResponse
    {
        $string = $request->get('name');
        $entreprises = $entrepriseRepo->findNameLike($string);
        $json = [];

        foreach ($entreprises as $entreprise) {
            $json[] = [
                'id' => $entreprise->getId(),
                'name' => $entreprise->getName(),
            ];
        }

        return new JsonResponse($json, 200);
    }
}
