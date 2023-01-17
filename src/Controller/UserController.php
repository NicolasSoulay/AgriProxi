<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserCreationFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    

    // Page de création de User
    #[Route('/create_user', name: 'createUser')]
    public function createUser(UserRepository $userRepo, Request $request): Response
    {   
        $message = '';
        $user = new User();
        $form = $this->createForm(UserCreationFormType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $user->setRoles(["ROLE_USER"]);
            $userRepo->save($user, true);
            $message = 'L\'utilisateur a bien été créé';
        }
        elseif($form->isSubmitted()){
            $message = 'Les informations ne sont pas valides, ou ce compte existe déjà';
        }
        return $this->render('user/create_user.html.twig', [
            'form_user' => $form->createView(),
            'message' => $message
        ]);
    }
}
