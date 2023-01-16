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

    #[Route('/create_user', name: 'createUser')]
    public function createUser(UserRepository $userRepo, Request $request): Response
    {   
        $user = new User();
        $form = $this->createForm(UserCreationFormType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $user->setRoles(["ROLE_USER"]);
            $userRepo->save($user, true);
        }
        return $this->render('user/create_user.html.twig', [
            'form_user' => $form->createView(),
        ]);
    }
}
