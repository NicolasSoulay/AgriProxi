<?php

namespace App\Controller;

use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AdresseController extends AbstractController
{
    #[Route('/adresse/ajax/ville/{name}', name: 'ajax_ville')]
    public function index(VilleRepository $cityRepo, Request $request): Response
    {
        $string = $request->get('name');
        $cities = $cityRepo->findNameLike($string);
        $json = [];

        foreach($cities as $city){
            $json[] = ['id' =>$city->getId(),'ville' => $city->getName(),
            'codeDepartement' => $city->getDepartement()->getCode()];
        }

        return new JsonResponse($json, 200);
    }
}
