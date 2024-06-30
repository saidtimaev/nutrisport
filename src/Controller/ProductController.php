<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductDietsType;
use App\Form\ProductType;
use Doctrine\ORM\EntityManager;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProductController extends AbstractController
{
    #[Route('admin/product', name: 'app_product')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    #[Route('admin/product/new', name: 'app_product_new')]
    public function product_new(EntityManagerInterface $entityManager, SluggerInterface $slugger, Request $request): Response
    {
        $productPicturesDirectory = $this->getParameter('product_pictures_directory');

        $product = new Product();
        
        $form = $this->createForm(ProductType::class, $product);
        $dietForm = $this->createForm(ProductDietsType::class);
        
        $form->handleRequest($request);
        $dietForm->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid() && $dietForm->isValid()) {

            $product = $form->getData();

            $productPicture = $form->get('picture')->getData();

            if($productPicture){

                $originalFilename = pathinfo($productPicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$productPicture->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $productPicture->move($productPicturesDirectory, $newFilename);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setPicture($newFilename);
                
            }

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/new.html.twig', [
            'addProductForm' => $form,
            'dietForm' => $dietForm
        ]);
    }

    #[Route('admin/product/{id}/show', name: 'app_product_show')]
    public function product_show(Product $product): Response
    {

        return $this->render('product/show.html.twig', [
            'product' => $product
        ]);
    }

    #[Route('admin/product/{id}/edit', name: 'app_product_edit')]
    public function product_edit(Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger, Request $request): Response
    {
        $currentProductPictureFilename = $product->getPicture();

        $productPicturesDirectory = $this->getParameter('product_pictures_directory');
        
        $form = $this->createForm(ProductType::class, $product);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $product = $form->getData();

            $productPicture = $form->get('picture')->getData();

            if($productPicture){

                $originalFilename = pathinfo($productPicture->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$productPicture->guessExtension();

                // Move the file to the directory where brochures are stored
                try {

                    $productPicture->move($productPicturesDirectory, $newFilename);

                } catch (FileException $e) {

                    $this->addFlash('warning','Un problème est survenu avec le chargement du fichier, veuillez réessayer.');

                    $this->redirectToRoute('app_product_edit', ['id' => $product->getId()]);

                }

                $oldPicturePath = $productPicturesDirectory.'/'.$currentProductPictureFilename;

                if (file_exists($oldPicturePath)) {
                    unlink($oldPicturePath);
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setPicture($newFilename);
                
            } 

            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product');
        }

        return $this->render('product/edit.html.twig', [
            'editProductForm' => $form
        ]);
    }
}
