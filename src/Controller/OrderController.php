<?php

namespace App\Controller;

use App\Entity\Motorcycle;  
use App\Entity\Car; 
use App\Entity\Order;
use App\Entity\Truck;
use App\Entity\Trailer;
use App\Entity\OrderDetail;
use App\Entity\Product;
use ReflectionClass; 

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;




class OrderController extends AbstractController
{

    //Order APIs
    #[Route('/api/submit-order', methods: ['POST'])]
    public function submitOrder(Request $request, EntityManagerInterface $entityManager): JsonResponse {
        $orderData = json_decode($request->getContent(), true);
                
        // Check if orderData contains orderDetails and they are not empty
        if (empty($orderData['orderDetails'])) {
            return $this->json(['error' => 'No products in order'], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        // Check if clientName is provided and not empty
        if (empty($orderData['clientName'])) {
            return $this->json(['error' => 'Client name is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $clientName = $orderData['clientName']; // Assign clientName from orderData
        $order = new Order();
        $order->setClientName($clientName);
    
        // Find the last order number for this client
        $lastOrder = $entityManager->getRepository(Order::class)
                                   ->findOneBy(['clientName' => $clientName], ['orderNumber' => 'DESC']);
        
        $newOrderNumber = $lastOrder ? $lastOrder->getOrderNumber() + 1 : 1;
        $order->setOrderNumber($newOrderNumber); // Set the new order number
    
        // Set the current datetime as the date_created
        $order->setDateCreated(new \DateTime());
    
        foreach ($orderData['orderDetails'] as $detailData) {
            // Check if productId exists in the array
            if (!isset($detailData['productId'])) {
                // Handle the missing productId case, maybe continue to the next iteration or return an error response
                return $this->json(['error' => 'Product ID is missing'], JsonResponse::HTTP_BAD_REQUEST);
            }
            $orderDetail = new OrderDetail();
            // Assuming $detailData['productId'] and $detailData['productType'] are provided
            $orderDetail->setProductId($detailData['productId']);
            $orderDetail->setProductType($detailData['productType']);
            
            // Add the OrderDetail to the Order
            $order->addOrderDetail($orderDetail);
            $entityManager->persist($orderDetail);
        }
        
        $entityManager->persist($order);
        $entityManager->flush();
    
        return $this->json(['success' => 'Order and order details saved successfully', 'id' => $order->getId()]);
    }
    
    
    #[Route('/api/orders/client', name: 'get_orders_by_client', methods: ['GET'])]
    public function getOrdersByClient(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $clientName = $request->query->get('name');
    
        $orderRepository = $entityManager->getRepository(Order::class);
        $orders = $orderRepository->findBy(['clientName' => $clientName]);
    
        $ordersData = [];
        foreach ($orders as $order) {
            $orderDetailsData = [];

            foreach ($order->getOrderDetails() as $detail) {
                // Assuming you have getter methods in OrderDetail entity
                $orderDetailsData[] = [
                    'productId' => $detail->getProductId(),
                    'productType' => $detail->getProductType(),
                    // Add other relevant detail fields here
                ];
            }

            $ordersData[] = [
                'id' => $order->getId(),
                'clientName' => $order->getClientName(),
                'orderNumber' => $order->getOrderNumber(),
                'dateCreated' => $order->getDateCreated()->format('Y-m-d H:i:s'),
                'orderDetails' => $orderDetailsData,
            ];
        }

        return $this->json($ordersData);
    }
    
    //Product API
    
    #[Route('/api/products/{id}', name: 'get_product_by_id', methods: ['GET'])]
    public function getProductDetails(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $productRepository = $entityManager->getRepository(Product::class);
        $product = $productRepository->find($id);
    
        if (!$product) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
    
        $reflectionClass = new ReflectionClass($product);
        $productType = $reflectionClass->getShortName(); // Gets the class name like 'Truck', 'Car', etc.
    
        $productData = [
            'id' => $product->getId(),
            'type' => $productType,
            'brand' => $product->getBrand(),
            'model' => $product->getModel(),
            // other common fields...
        ];
    
        // Add type-specific fields dynamically
        switch ($productType) {
            case 'Motorcycle':
                $productData['engineCapacity'] = $product->getEngineCapacity();
                $productData['colour'] = $product->getColour();
                break;
            case 'Car':
                $productData['engineCapacity'] = $product->getEngineCapacity();
                $productData['colour'] = $product->getColour();
                $productData['numberOfDoors'] = $product->getNumberOfDoors();
                $productData['category'] = $product->getCategory();
                break;
            case 'Truck':
                $productData['engineCapacity'] = $product->getEngineCapacity();
                $productData['colour'] = $product->getColour();
                $productData['numberOfBeds'] = $product->getNumberOfBeds();
                break;
            case 'Trailer':
                $productData['loadCapacity'] = $product->getLoadCapacity();
                $productData['numberOfAxles'] = $product->getNumberOfAxles();
                break;
            // Add other cases if necessary
        }
    
        return $this->json($productData);
    }


    //Motorcycle APIs
    #[Route('/api/submit-motorcycle', name: 'submit_motorcycle', methods: ['POST', 'OPTIONS'])]
    public function submitMotorcycle(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
    
        $motorcycle = new Motorcycle();
        $motorcycle->setBrand($data['brand'] ?? '');
        $motorcycle->setModel($data['model'] ?? '');
        $motorcycle->setEngineCapacity($data['engineCapacity'] ?? '');
        $motorcycle->setColour($data['colour'] ?? '');
    
        // Validate the entity
        $errors = $validator->validate($motorcycle);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
    
        $entityManager->persist($motorcycle);
        $entityManager->flush();
    
        return $this->json(['success' => 'Motorcycle added successfully', 'id' => $motorcycle->getId()]);
    }
    
    #[Route('/api/motorcycles', name: 'get_motorcycles', methods: ['GET'])]
    public function getMotorcycles(EntityManagerInterface $entityManager): JsonResponse
    {
        $motorcycleRepository = $entityManager->getRepository(Motorcycle::class);
        $motorcycles = $motorcycleRepository->findAll();

        $motorcycleData = [];
        foreach ($motorcycles as $motorcycle) {
            $motorcycleData[] = [
                'id' => $motorcycle->getId(),
                'brand' => $motorcycle->getBrand(),
                'model' => $motorcycle->getModel(),
                'engineCapacity' => $motorcycle->getEngineCapacity(),
                'colour' => $motorcycle->getColour()
            ];
        }

        return $this->json($motorcycleData);
    }

    //Car APIs

    #[Route('/api/submit-car', name: 'submit_car', methods: ['POST', 'OPTIONS'])]
    public function submitCar(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $car = new Car();
        $car->setBrand($data['brand']);
        $car->setModel($data['model']);
        $car->setEngineCapacity($data['engineCapacity']);
        $car->setColour($data['colour']);
        $car->setNumberOfDoors($data['numberOfDoors']);
        $car->setCategory($data['category']);

        // Validate the entity
        $errors = $validator->validate($car);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($car);
        $entityManager->flush();

        return $this->json(['success' => 'Car added successfully', 'id' => $car->getId()]);
    }

    #[Route('/api/cars', name: 'get_cars', methods: ['GET'])]
    public function getCars(EntityManagerInterface $entityManager, ): JsonResponse
    {
        $carRepository = $entityManager->getRepository(Car::class);
        $cars = $carRepository->findAll();

        $carData = [];
        foreach ($cars as $car) {
            $carData[] = [
                'id' => $car->getId(),
                'brand' => $car->getBrand(),
                'model' => $car->getModel(),
                'engineCapacity' => $car->getEngineCapacity(),
                'colour' => $car->getColour(),
                'numberOfDoors' => $car->getNumberOfDoors(),
                'category' => $car->getCategory()
            ];
        }

        return $this->json($carData);
    }


    //Truck APIs
    #[Route('/api/submit-truck', name: 'submit_truck', methods: ['POST'])]
    public function submitTruck(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $truck = new Truck();
        $truck->setBrand($data['brand']);
        $truck->setModel($data['model']);
        $truck->setEngineCapacity($data['engineCapacity']);
        $truck->setColour($data['colour']);
        $truck->setNumberOfBeds($data['numberOfBeds']);

        // Validate the entity
        $errors = $validator->validate($truck);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($truck);
        $entityManager->flush();

        return $this->json(['success' => 'Truck added successfully', 'id' => $truck->getId()]);
    }

    #[Route('/api/trucks', name: 'get_trucks', methods: ['GET'])]
    public function getTrucks(EntityManagerInterface $entityManager): JsonResponse
    {
        $truckRepository = $entityManager->getRepository(Truck::class);
        $trucks = $truckRepository->findAll();

        $truckData = [];
        foreach ($trucks as $truck) {
            $truckData[] = [
                'id' => $truck->getId(),
                'brand' => $truck->getBrand(),
                'model' => $truck->getModel(),
                'engineCapacity' => $truck->getEngineCapacity(),
                'colour' => $truck->getColour(),
                'numberOfBeds' => $truck->getNumberOfBeds()
            ];
        }

        return $this->json($truckData);
    }

    //Trailer APIs

    #[Route('/api/submit-trailer', name: 'submit_trailer', methods: ['POST', 'OPTIONS'])]
    public function submitTrailer(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $trailer = new Trailer();
        $trailer->setBrand($data['brand']);
        $trailer->setModel($data['model']);
        $trailer->setLoadCapacity($data['loadCapacity']);
        $trailer->setNumberOfAxles($data['numberOfAxles']);

        // Validate the entity
        $errors = $validator->validate($trailer);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['error' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($trailer);
        $entityManager->flush();

        return $this->json(['success' => 'Trailer added successfully', 'id' => $trailer->getId()]);
    }

    #[Route('/api/trailers', name: 'get_trailers', methods: ['GET'])]
    public function getTrailers(EntityManagerInterface $entityManager): JsonResponse
    {
        $trailerRepository = $entityManager->getRepository(Trailer::class);
        $trailers = $trailerRepository->findAll();

        $trailerData = [];
        foreach ($trailers as $trailer) {
            $trailerData[] = [
                'id' => $trailer->getId(),
                'brand' => $trailer->getBrand(),
                'model' => $trailer->getModel(),
                'loadCapacity' => $trailer->getLoadCapacity(),
                'numberOfAxles' => $trailer->getNumberOfAxles()
            ];
        }

        return $this->json($trailerData);
    }

    

}   


