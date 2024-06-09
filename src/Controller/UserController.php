<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    // Affiche le profil utilisateur
    #[Route('/user/{id}', name: 'app_user_profile')]
    public function showProfile(User $user): Response
    {
        return $this->render('user/profile.html.twig', [
            
        ]);
    }
}
