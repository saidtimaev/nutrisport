<?php

namespace App\Controller;

use App\Repository\DietRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DietController extends AbstractController
{
    #[Route('admin/diet', name: 'app_diet')]
    public function index(DietRepository $dietRepository): Response
    {

        $diets = $dietRepository->findAll();

        return $this->render('diet/index.html.twig', [
            'diets' => $diets,
        ]);
    }
}
