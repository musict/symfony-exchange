<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\History;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/api', name: 'api_')]
class HistoryController extends AbstractController
{
    #[Route('/history', name: 'show_history')]
    public function show(ManagerRegistry $doctrine, PaginatorInterface $paginator, Request $request)
    {
        // Get the sorting parameters from the request
        $sortField = $request->query->get('sort', 'id'); // Default sorting by 'id'
        $sortDirection = $request->query->get('direction', 'asc'); // Default sorting direction

        // Query the products from the repository
        $query = $doctrine->getRepository(History::class)->findAll($sortField, $sortDirection);

        // Paginate the results
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Page number from the request
            2 // Items per page
        );

        return $this->render('history.html.twig', [
            'pagination' => $pagination,
        ]);

    }

    #[Route('/exchange/values', name: 'exchange_values', methods:['post'])]
    public function exchange(ManagerRegistry $doctrine, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data)) {
            throw new \Exception('Invalid JSON data');
        }

        $firstIn  = $data['first'] ?? null;
        $secondIn = $data['second'] ?? null;

        if (empty($firstIn) || empty($secondIn)) {
            throw new \Exception('Fields `first` and `second` are required');
        }

        if (!is_int($firstIn) || !is_int($secondIn)) {
            throw new \Exception('Fields `first` and `second` must be integer');
        }

        $entityManager = $doctrine->getManager();

        $history = new History();
        $history->setFirstIn($firstIn);
        $history->setSecondIn($secondIn);

        $entityManager->persist($history);
        $entityManager->flush();

        $history->setFirstOut($secondIn);
        $history->setSecondOut($firstIn);

        $entityManager->persist($history);
        $entityManager->flush();

        return $this->json('Data was processed successfully');
    }
}
