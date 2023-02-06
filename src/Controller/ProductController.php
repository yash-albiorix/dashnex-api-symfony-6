<?php

namespace App\Controller;

use App\Entity\Product;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/api', name: 'api_')]
 class ProductController extends AbstractController
{
    #[Route('/products', name: 'app_product',  methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $products = $doctrine
            ->getRepository(Product::class)
            ->findAll();
  
        $data = [];
  
        foreach ($products as $product) {
           $data[] = [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'image' => $product->getImage(),
            'price' => $product->getPrice(),
            'description' => $product->getDescription(),
            'created_at' => $product->getCreatedAt(),
            'updated_at' => $product->getUpdatedAt(),
           ];
        }
  
        return $this->json($data);
    }

    #[Route('/product', name: 'new_product',  methods: ['POST'])]
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $parameters = json_decode($request->getContent(), true);

        $entityManager = $doctrine->getManager();
  
        $date = new \DateTimeImmutable('@'.strtotime('now'));

        $product = new Product();
        $product->setTitle($parameters['title']);
        $product->setImage($parameters['image']);
        $product->setPrice($parameters['price']);
        $product->setDescription($parameters['description']);
        $product->setCreatedAt($date);
        $product->setUpdatedAt($date);
  
        $entityManager->persist($product);
        $entityManager->flush();
  
        return $this->json( $product);
    }

    #[Route('/product/{id}', name: 'show_product',  methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $product = $doctrine->getRepository(Product::class)->find($id);
  
        if (!$product) {
            return $this->json('No product found for id' . $id, 404);
        }
  
        $data =  [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'image' => $product->getImage(),
            'price' => $product->getPrice(),
            'description' => $product->getDescription(),
            'created_at' => $product->getCreatedAt(),
            'updated_at' => $product->getUpdatedAt(),
        ];
          
        return $this->json($data);
    }

    #[Route('/product/{id}', name: 'edit_product',  methods: ['PUT'])]
    public function edit(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);
  
        if (!$product) {
            return $this->json('No product found for id' . $id, 404);
        }
        $parameters = json_decode($request->getContent(), true);
        $date = new \DateTimeImmutable('@'.strtotime('now'));

        $product->setTitle($parameters['title']);
        $product->setImage($parameters['image']);
        $product->setPrice($parameters['price']);
        $product->setDescription($parameters['description']);
        $product->setUpdatedAt($date);
  
        $product->setName($request->request->get('name'));
        $product->setDescription($request->request->get('description'));
        $entityManager->flush();
  
        $data =  [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'image' => $product->getImage(),
            'price' => $product->getPrice(),
            'description' => $product->getDescription(),
            'created_at' => $product->getCreatedAt(),
            'updated_at' => $product->getUpdatedAt(),
        ];
          
        return $this->json($data);
    }

    #[Route('/product/{id}', name: 'delete_product',  methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $product = $entityManager->getRepository(Product::class)->find($id);
  
        if (!$product) {
            return $this->json('No product found for id' . $id, 404);
        }
  
        $entityManager->remove($product);
        $entityManager->flush();
  
        return $this->json('Deleted a product successfully with id ' . $id);
    }
}
