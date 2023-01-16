<?php

namespace App\Controller;

use App\Entity\Entreprise;
use App\Form\EntrepriseCreationFormType;
use App\Repository\EntrepriseRepository;
use App\Repository\TypeEntrepriseRepository;
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

    #[Route('/create_entreprise', name: 'createEntreprise')]
    public function createEntreprise(EntrepriseRepository $entrepriseRepo, TypeEntrepriseRepository $typeEntrepRepo, Request $request): Response
    {   
        $typeEntreprise = $typeEntrepRepo->findAll();
        $message = '';
        $entreprise = new Entreprise();
        $form = $this->createForm(EntrepriseCreationFormType::class, $entreprise);//, ['typeEntreprise' => $typeEntreprise]
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entrepriseRepo->save($entreprise, true);
            $message = 'L\'entreprise a bien été créé';
        }
        elseif($form->isSubmitted()){
            $message = 'Les informations ne sont pas valides, ou cette entreprise existe déjà';
        }

        return $this->render('entreprise/create_entreprise.html.twig', [
            'form_entreprise' => $form->createView(),
            'message' => $message,
            'var' => var_dump($typeEntreprise)
        ]);
    }
}
