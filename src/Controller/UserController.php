<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use App\Form\UserMyProfileEditFormType;
use App\Form\AdminMyProfileEditFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }

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

        // Création du formulaire d'édition
        $form = $this->createForm(AdminMyProfileEditFormType::class, $user);

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
        
        $mailActuel = $user->getEmail();

        // Création du formulaire d'édition
        $form = $this->createForm(UserMyProfileEditFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if($mailActuel != $form->get('email')->getData()){

                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('admin@nutrisport.fr', 'Bot NutriSport'))
                    ->to($user->getEmail())
                    ->subject('Lien de confirmation de votre nouveau email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                );

                $user->setVerified(false);

                $this->addFlash('warning', 'Mail modifié.');

                // Message flash de notification que le mail de confirmation a été envoyé
                $this->addFlash('warning', 'Un message vous a été envoyé à votre adresse électronique pour confirmer votre nouveau mail.');
            }

            if($form->get('password')->getData() && $form->get('oldPassword')->getData()){

                if($userPasswordHasher->isPasswordValid($user, $form->get('oldPassword')->getData())){
                    $user->setPassword(
                        $userPasswordHasher->hashPassword(
                            $user,
                            $form->get('password')->getData()
                        )     
                    );

                    $this->addFlash('success', 'Mot de passe modifié avec succès.');

                } else {
                    $this->addFlash('warning', 'Veuillez remplir les 3 champs correctement.');
                }
            }

            // Prepare PDO
            $entityManager->persist($user);
            // Execute PDO
            $entityManager->flush();
                        
            return $this->redirectToRoute('app_user_edit_my_profile', ['id'=> $user->getId()]);

        }

        return $this->render('user/userEditMyProfile.html.twig', [
            'editForm' => $form,
            'user' => $user,
        ]);
    }

    // Ban ou unban un utilisateur
    #[Route('admin/user/{id}/ban', name: 'app_ban_unban_user')]
    public function ban_unban_user_profile(User $user, EntityManagerInterface $entityManager): Response
    {

        if($user->isBanned()){
            $user->setBanned(false);
            $this->addFlash('success', 'L\'utilisateur a été unban');
        } else {
            $user->setBanned(true);
            $this->addFlash('success', 'L\'utilisateur a été ban');
        }

        // Prepare PDO
        $entityManager->persist($user);
        // Execute PDO
        $entityManager->flush();

        return $this->redirectToRoute('app_manage_user', ['id'=> $user->getId()]);

    }
}
