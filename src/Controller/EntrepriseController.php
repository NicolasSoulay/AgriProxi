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
    #[Route('/entreprise', name: 'app_entreprise')]
    public function index(): Response
    {
        return $this->render('entreprise/index.html.twig', [
            'controller_name' => 'EntrepriseController',
        ]);
    }

    //Page de création d'entreprise
    #[Route('/create_entreprise', name: 'createEntreprise')]
    public function createEntreprise(EntrepriseRepository $entrepriseRepo, UserRepository $userRepo, Request $request): Response
    {   
        $message = '';
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseCreationFormType::class, $entreprise);
        $form->handleRequest($request);
        $userId = $this->getUser()->getId();
        $user = $userRepo->find($userId);
        if($form->isSubmitted() && $form->isValid()){
            $entrepriseRepo->save($entreprise, true);
            $user->setEntreprise($entreprise);
            $userRepo->save($user, true);
            $message = 'L\'entreprise a bien été créée';
            //return  $this->redirectToRoute('app_entreprise');
        }
        elseif($form->isSubmitted()){
            $message = 'Les informations ne sont pas valides, ou cette entreprise existe déjà';
        }

        return $this->render('entreprise/create_entreprise.html.twig', [
            'form_entreprise' => $form->createView(),
            'message' => $message,
        ]);
    }

    #[Route('/entreprise/createAdresse', name: 'createAdresse')]
    public function createAdresse(Request $request, AdresseRepository $adresseRepo): Response
    {
        $message = '';
        $adresse = new Adresse();
        $form = $this->createForm(AdresseCreationFormType::class, $adresse);
        $form->handleRequest($request);
        $entreprise = $this->getUser()->getEntreprise();
        if($form->isSubmitted() && $form->isValid()){
            $adresse->setEntreprise($entreprise);
            $adresseRepo->save($adresse, true);
            $message = 'L\'adresse a bien été créée';
            //return  $this->redirectToRoute('app_entreprise');
        }
        elseif($form->isSubmitted()){
            $message = 'Les informations ne sont pas valides';
        }
        return $this->render('entreprise/create_adresse.html.twig', [
            'form_adresse' => $form->createView(),
            'message' => $message
        ]);
    }
}
