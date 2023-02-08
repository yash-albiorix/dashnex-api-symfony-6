<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\Product;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api', name: 'api_')]
class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart',  methods: ['GET'])]
    public function index(ManagerRegistry $doctrine): JsonResponse
    {
        $carts = $doctrine->getRepository(Cart::class)->findAll();
  
        $data = [];
  
        foreach ($carts as $cart) {

            $product = array(
                'id' => $cart->getProduct()->getId(),
                'title' => $cart->getProduct()->getTitle(),
                'image' => $cart->getProduct()->getImage(),
                'price' => $cart->getProduct()->getPrice(),
                'description' => $cart->getProduct()->getDescription(),
                'created_at' => $cart->getProduct()->getCreatedAt(),
                'updated_at' => $cart->getProduct()->getUpdatedAt(),
            );

           $data[] = [
            'id' => $cart->getId(),
            'product' => $product,
            'quantity' => $cart->getQuantity(),
            'created_at' => $cart->getCreatedAt(),
            'updated_at' => $cart->getUpdatedAt(),
           ];
        }

        return $this->json(
            [ 
                'status' => true, 
                'msg' => '', 
                'data' => $data
            ]
        );
  
        return $this->json($data);
    }
    
    #[Route('/cart', name: 'new_cart',  methods: ['POST'])]
    public function new(ManagerRegistry $doctrine, Request $request): Response
    {
        $parameters = json_decode($request->getContent(), true);
        $entityManager = $doctrine->getManager();
        $date = new \DateTimeImmutable('@'.strtotime('now'));

        $cart = new Cart();
        $product = $doctrine->getRepository(Product::class)->find($parameters['productId']);
        
        if (!$product) {
            return $this->json(
                [
                    'status' => false, 
                    'msg' => 'Product Not Found', 
                    'data' => []
                ]
            );
        }
        $cart->setProduct($product);
        $cart->setQuantity($parameters['quantity']);
        $cart->setCreatedAt($date);
        $cart->setUpdatedAt($date);
    
        $entityManager->persist($cart);

        try {
            $entityManager->flush();
         }
         catch (UniqueConstraintViolationException $e) {
             if($e->getCode() == 1062){
                return $this->json(
                    [ 
                        'status' => false, 
                        'msg' => 'Product is already in Cart', 
                        'data' => []
                    ]
                );
             }
         }


        $productData = array(
            'id' => $cart->getProduct()->getId(),
            'title' => $cart->getProduct()->getTitle(),
            'image' => $cart->getProduct()->getImage(),
            'price' => $cart->getProduct()->getPrice(),
            'description' => $cart->getProduct()->getDescription(),
            'created_at' => $cart->getProduct()->getCreatedAt(),
            'updated_at' => $cart->getProduct()->getUpdatedAt(),
        );

        return $this->json(
            [ 
                'status' => true, 
                'msg' => '', 
                'data' => [
                    'id' => $cart->getId(),
                    'productId' => $productData,
                    'quantity' => $cart->getQuantity(),
                    'created_at' => $cart->getCreatedAt(),
                    'updated_at' => $cart->getUpdatedAt()
                ]
            ]
        );
    }

    #[Route('/cart/{id}', name: 'show_cart',  methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): Response
    {
        $cart = $doctrine->getRepository(Cart::class)->find($id);
  
        if (!$cart) {
            return $this->json(['status' => false, 'msg' => 'Cart Not Found', 'data' => []]);
        }

        $product = array(
            'id' => $cart->getProduct()->getId(),
            'title' => $cart->getProduct()->getTitle(),
            'image' => $cart->getProduct()->getImage(),
            'price' => $cart->getProduct()->getPrice(),
            'description' => $cart->getProduct()->getDescription(),
            'created_at' => $cart->getProduct()->getCreatedAt(),
            'updated_at' => $cart->getProduct()->getUpdatedAt(),
        );

       $data = [
        'id' => $cart->getId(),
        'product' => $product,
        'quantity' => $cart->getQuantity(),
        'created_at' => $cart->getCreatedAt(),
        'updated_at' => $cart->getUpdatedAt(),
       ];

        return $this->json(
            [ 
                'status' => true, 
                'msg' => '', 
                'data' => $data
            ]
        );
          
    }

    #[Route('/cart/{id}', name: 'edit_cart',  methods: ['PUT'])]
    public function edit(ManagerRegistry $doctrine, Request $request, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $cart = $entityManager->getRepository(Cart::class)->find($id);
  
        if (!$cart) {
            return $this->json(['status' => false, 'msg' => 'Cart Not Found', 'data' => []]);
        }

        $parameters = json_decode($request->getContent(), true);
        $date = new \DateTimeImmutable('@'.strtotime('now'));

        $cart->setQuantity($parameters['quantity']);
        $cart->setUpdatedAt($date);
  
        $entityManager->flush();
  
        $product = array(
            'id' => $cart->getProduct()->getId(),
            'title' => $cart->getProduct()->getTitle(),
            'image' => $cart->getProduct()->getImage(),
            'price' => $cart->getProduct()->getPrice(),
            'description' => $cart->getProduct()->getDescription(),
            'created_at' => $cart->getProduct()->getCreatedAt(),
            'updated_at' => $cart->getProduct()->getUpdatedAt(),
        );

       $data = [
        'id' => $cart->getId(),
        'product' => $product,
        'quantity' => $cart->getQuantity(),
        'created_at' => $cart->getCreatedAt(),
        'updated_at' => $cart->getUpdatedAt(),
       ];

        return $this->json(
            [ 
                'status' => true, 
                'msg' => '', 
                'data' => $data
            ]
        );
    }

    #[Route('/cart/{id}', name: 'delete_cart',  methods: ['DELETE'])]
    public function delete(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $cart = $entityManager->getRepository(Cart::class)->find($id);
  
        if (!$cart) {
            return $this->json(['status' => false, 'msg' => 'Cart Not Found', 'data' => []]);
        }
  
        $entityManager->remove($cart);
        $entityManager->flush();
  
        return $this->json(['status' => true, 'msg' => 'Deleted a Cart successfully with id ' . $id, 'data' => []]);
    }
}
