<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\History;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api', name: 'api_')]
class HistoryController extends AbstractController
{
    #[Route('/history', name: 'show_history')]
    public function show(ManagerRegistry $doctrine): JsonResponse
    {
        $history = $doctrine
            ->getRepository(History::class)
            ->findAll();

        $data = [];

        foreach ($history as $item) {
            $data[] = [
                'id'         => $item->getId(),
                'first_in'   => $item->getFirstIn(),
                'second_in'  => $item->getSecondIn(),
                'first_out'  => $item->getFirstOut(),
                'second_out' => $item->getSecondOut(),
                'created_at' => $item->getCreatedAt(),
                'updated_at' => $item->getUpdatedAt(),
            ];
        }

        return $this->json($data);
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
