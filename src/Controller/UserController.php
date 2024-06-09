<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\MyProfileEditFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('user/index.html.twig', [
            'users' => $users,
        ]);
    }

    // Affiche mon profil utilisateur
    #[Route('user/{id}', name: 'app_my_profile')]
    public function show_my_profile(User $user): Response
    {
        return $this->render('user/myProfile.html.twig', [
            'user' => $user
        ]);
    }

    // Affiche la page de gestion d'un utilisateur
    #[Route('admin/user/{id}', name: 'app_manage_user')]
    public function manage_user_profile(User $user): Response
    {
        return $this->render('user/userProfile.html.twig', [
            'user' => $user
        ]);
    }

    // Affiche la page d'édition de profils admin'
    #[Route('admin/user/{id}/edit', name: 'app_admin_edit_my_profile')]
    public function admin_edit_my_profile(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // Si l'utilisateur en session a le role admin alors $roleAdmin = true
        $isAdmin = (in_array("ROLE_ADMIN", $this->getUser()->getRoles())); 

        // Création du formulaire d'édition
        $form = $this->createForm(MyProfileEditFormType::class, $user, ['isAdmin' => $isAdmin]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {


            // Si un mot de passe a été saisi dans le formulaire
            if($form->get('password')->getData()){
                // alors définir le mot de passe 
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )     
                );
            } 
    
            // Prepare PDO
            $entityManager->persist($user);
            // Execute PDO
            $entityManager->flush();

            $this->addFlash('success', 'Profil modifié avec succès.');

            return $this->redirectToRoute('app_my_profile', ['id'=> $user->getId()]);
        }

        return $this->render('user/editMyProfile.html.twig', [
            'editForm' => $form,
            'user' => $user,
        ]);
    }

    // Affiche la page d'édition de profils admin'
    #[Route('user/{id}/edit', name: 'app_user_edit_my_profile')]
    public function user_edit_my_profile(User $user, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        // Si l'utilisateur en session a le role admin alors $roleAdmin = true
        $isUser = (in_array("ROLE_USER", $this->getUser()->getRoles())); 

        // Création du formulaire d'édition
        $form = $this->createForm(MyProfileEditFormType::class, $user, ['isUser' => $isUser, 'passwordUpdate' => null, 'mailUpdate' => null]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Prepare PDO
            $entityManager->persist($user);
            // Execute PDO
            $entityManager->flush();

            $this->addFlash('success', 'Profil modifié avec succès.');

            return $this->redirectToRoute('app_my_profile', ['id'=> $user->getId()]);
        }

        return $this->render('user/editMyProfile.html.twig', [
            'editForm' => $form,
            'user' => $user,
        ]);
    }
    
}
