<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\Entreprise;
use App\Entity\User;
use App\Form\AdresseCreationFormType;
use App\Form\EntrepriseCreationFormType;
use App\Repository\AdresseRepository;
use App\Repository\EntrepriseRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EntrepriseController extends AbstractController
{
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
            $userRepo->save($user, true);
            if ($request->get('id')) {
                $message = 'L\'entreprise a bien été modifiée';
                return $this->redirectToRoute('app_user', [
                    'message' => '0'
                ]);
            } else {
                $message = 'L\'entreprise a bien été créée';
                return $this->redirectToRoute('createAdresse');
            }
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
        return $this->formEntreprise($entreprise, $entrepriseRepo, $userRepo, $request, $user);
    }


    //Affichage Formulaire pour l'entité Adresse
    private function formAdresse(Adresse $adresse, AdresseRepository $adresseRepo, Request $request, Entreprise $entreprise)
    {
        $message = '';
        $form = $this->createForm(AdresseCreationFormType::class, $adresse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $adresse->setEntreprise($entreprise);
            $adresseRepo->save($adresse, true);
            if ($request->get('id')) {
                $message = 'L\'adresse a bien été modifiée';
                return $this->redirectToRoute('app_user', [
                    'message' => '2'
                ]);
            } else {
                $message = 'L\'adresse a bien été créée';
                if($this->getUser()){
                    return $this->redirectToRoute('app_user', [
                        'message' => '1'
                    ]);
                }
                else{
                    return $this->redirectToRoute('app_login');
                }
            }
        } elseif ($form->isSubmitted()) {
            $message = 'Les informations ne sont pas valides';
        }
        return $this->render('entreprise/create_adresse.html.twig', [
            'form_adresse' => $form->createView(),
            'message' => $message
        ]);
    }

    //Page de création d'adresse
    #[Route('/create_adresse', name: 'createAdresse')]
    public function createAdresse(AdresseRepository $adresseRepo, Request $request, EntrepriseRepository $entrepriseRepo): Response
    {
        $entrepriseId = $entrepriseRepo->getLastId();
        $entreprise = $entrepriseRepo->find($entrepriseId);
        $adresse = new Adresse();
        return $this->formAdresse($adresse, $adresseRepo, $request, $entreprise);
    }

    //Page de modification d'adresse
    #[Route('/update_adresse/{id}', name: 'updateAdresse')]
    public function updateAdresse(Adresse $adresse, AdresseRepository $adresseRepo, Request $request): Response
    {
        $entreprise = $this->getUser()->getEntreprise();
        return $this->formAdresse($adresse, $adresseRepo, $request, $entreprise);
    }

    #[Route('/delete_adresse/{id}', name: 'deleteAdresse')]
    public function deleteAdresse(Adresse $adresse, AdresseRepository $adresseRepo): Response
    {
        $adresseRepo->remove($adresse, true);
        return $this->redirectToRoute('app_user',[
            'message' => '3'
        ]);
    }
}
