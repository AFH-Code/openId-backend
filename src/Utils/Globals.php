<?php

namespace App\Utils;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class Globals 
{
    private ManagerRegistry $managerRegistry;
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    public function success(array $data = null, $message = 'success', int $codeHttp = 200): JsonResponse
    {
        return new JsonResponse([
            'status' => 1,
            'message' => $message,
            'data' => $data
        ], $codeHttp);
    }

    public function success_void(array $data = null, $message = 'success', int $codeHttp = 200): JsonResponse
    {
        return new JsonResponse([
            'status' => 1,
            'message' => $message,
            'data' => []
        ], $codeHttp);
    }

    public function error(array $error = ErrorHttp::ERROR): JsonResponse
    {
        return new JsonResponse([
            'status' => 0,
            'message' => $error['message'] ?? 'error',
        ], $error['code'] ?? 500); //, Response::HTTP_BAD_REQUEST
    }

    public function jsondecode()
    {
        try {
            return file_get_contents('php://input')?
            json_decode(file_get_contents('php://input')): [];          
        } catch (\Exception $e){
            return [];
        }
    }

    public function em(): ObjectManager
    {
        return $this->managerRegistry->getManager();
    }
}