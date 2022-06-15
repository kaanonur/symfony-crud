<?php

namespace App\Controller;

use App\Entity\Order;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/api/order', name: 'app_order')]
    public function index(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $orders = $entityManager->getRepository(Order::class)->findAll();

        $response = [];
        foreach ($orders as $order) {
            $response[] = [
                'id' => $order->getId(),
                'order_code' => $order->getOrderCode(),
                'user_id' => $order->getUserId(),
                'product_id' => $order->getProductId(),
                'quantity' => $order->getQuantity(),
                'address' => $order->getAddress(),
                'shipping_date' => $order->getShippingDate()
            ];
        }
        return $this->json($response);
    }

    #[Route('/api/create-order', name: 'app_create_order', methods: ['POST'])]
    public function create(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $order = new Order;
        $order->setOrderCode(time().rand(10000,99999));
        $order->setProductId($request->request->get('product_id'));
        $order->setQuantity($request->request->get('quantity'));
        $order->setAddress($request->request->get('address'));
        $order->setUserId($this->getUser()->getId());
        $entityManager->persist($order);
        $entityManager->flush();

        return $this->json([
            'message' => 'Successfully Created'
        ]);
    }

    #[Route('/api/update-order/{id}', name: 'app_update_order', methods: ['POST'])]
    public function update(Request $request,ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $order = $entityManager->getRepository(Order::class)->find($id);

        if (!$order) {

            return $this->json(['message' => 'Order not found']);
        } else {
            if (!$order->getShippingDate()) {
                $order->setProductId($request->request->get('product_id'));
                $order->setQuantity($request->request->get('quantity'));
                $order->setAddress($request->request->get('address'));

                $entityManager->flush();

                return $this->json(['message' => 'Successfully Updated']);

            } else {

                return $this->json(['message' => 'You can\'t update this order. This order has shipped']);
            }
        }
    }

    #[Route('/api/order-details/{id}', name: 'app_order_details', methods: ['GET'])]
    public function show(ManagerRegistry $doctrine, int $id): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $order = $entityManager->getRepository(Order::class)->find($id);

        $response = [
            'id' => $order->getId(),
            'order_code' => $order->getOrderCode(),
            'user_id' => $order->getUserId(),
            'product_id' => $order->getProductId(),
            'quantity' => $order->getQuantity(),
            'address' => $order->getAddress(),
            'shipping_date' => $order->getShippingDate()
        ];


        return $this->json($response);
    }
}
