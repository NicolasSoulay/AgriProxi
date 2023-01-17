<?php

namespace App\Controller;

use App\Entity\Adresse;
use App\Entity\User;
use App\Form\UserCreationFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    // Page d'affichage dans Mon compte/ Mes informations
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {   
        $user = $this->getUser();
        $entreprise = $user->getEntreprise();
        $adresses = $entreprise->getAdresses();
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'entreprise' => $entreprise,
            'adresses' => $adresses,
        ]);
    }

    //Affichage Formulaire pour l'entité User
    private function formUser(UserRepository $userRepo, Request $request, User $user):Response
    {
        $message = '';
        $form = $this->createForm(UserCreationFormType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user->setRoles(["ROLE_USER"]);
            $userRepo->save($user, true);
            if($request->get('id')){
                $message = 'L\'utilisateur a bien été modifié';
            }else{
                $message = 'L\'utilisateur a bien été créé';
            }
        }
        elseif($form->isSubmitted()){
            $message = 'Les informations ne sont pas valides, ou ce compte existe déjà';
        }
        return $this->render('user/create_user.html.twig', [
            'form_user' => $form->createView(),
            'message' => $message
        ]);
    }

    // Page de création du User
    #[Route('/create_user', name: 'createUser')]
    public function createUser(UserRepository $userRepo, Request $request)
    {   
        $user = new User();
        return $this->formUser($userRepo, $request, $user);
    }

    //Page de modification du User
    #[Route('/update_user/{id}', name: 'updateUser')]
    public function updateUser(User $user, UserRepository $userRepo, Request $request)
    {   
        return $this->formUser($userRepo, $request, $user);
    }
}
