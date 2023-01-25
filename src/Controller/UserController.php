<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserCreationFormType;
use App\Repository\EntrepriseRepository;
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
        $typeEntreprise = $entreprise->getTypeEntreprise();
        $message = '';
        if (isset($_GET['message'])) {
            switch ($_GET['message']) {
                case '0':
                    $message = 'L\'entreprise a bien été modifié';
                    break;
                case '1':
                    $message = 'L\'adresse a bien été créée';
                    break;
                case '2':
                    $message = 'L\'adresse a bien été modifiée';
                    break;
                case '3':
                    $message = 'L\'adresse a bien été supprimée';
                    break;
                case '4':
                    $message = 'L\'utilisateur a bien été modifiée';
                    break;
                case '5':
                    $message = 'Vous devez renseigner une adresse valide pour accéder à la carte des producteurs';
                    break;
                default:
                    $message = '';
            }
        }
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'entreprise' => $entreprise,
            'adresses' => $adresses,
            'message' => $message,
            'typeEntreprise' => $typeEntreprise,
        ]);
    }

    //Affichage Formulaire pour l'entité User
    private function formUser(UserRepository $userRepo, Request $request, User $user): Response
    {
        $message = '';
        $form = $this->createForm(UserCreationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setRoles(["ROLE_USER"]);
            $userRepo->save($user, true);
            if ($request->get('id')) {
                $message = 'L\'utilisateur a bien été modifié';
                return $this->redirectToRoute('app_user', [
                    'message' => '4',
                ]);
            } else {
                $message = 'L\'utilisateur a bien été créé';
                return $this->redirectToRoute('createEntreprise');
            }
        } elseif ($form->isSubmitted()) {
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

    //Suppression du User
    #[Route('/delete_user/{id}', name: 'deleteUser')]
    public function deleteUser(string $id, User $user, UserRepository $userRepo, EntrepriseRepository $entrepriseRepo)
    {
        $user = $userRepo->find($id);
        $entreprise = $user->getEntreprise();

        //Suppression des lignes devis
        $linesDevis = $user->getLigneDevis();
        foreach($linesDevis as $lineDevis){
            $user->removeLigneDevi($lineDevis);
        }
        //Suppression des devis
        $devis = $entreprise->getDevis();
        foreach($devis as $devi){
            $entreprise->removeDevi($devi);
        }
        if(count($entreprise->getUsers())===1){
            //Suppression des adresses
            $adresses = $entreprise->getAdresses();
            foreach($adresses as $adresse){
                $entreprise->removeAdress($adresse);
            }
            //Suppression des produits
            $products = $entreprise->getProduits();
            foreach($products as $product){
                $entreprise->removeProduit($product);
            }
            //Suppression du user
            $userRepo->remove($user, true);
            //Suppression de l'entreprise
            $entrepriseRepo->remove($entreprise, true);
        }
        else{
            //Suppression du user
            $userRepo->remove($user, true);
        }

    return $this->redirectToRoute('app_login');
    }
}
