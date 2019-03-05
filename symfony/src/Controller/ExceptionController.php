<?php

namespace App\Controller;

use App\Response\JsonApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

class ExceptionController extends AbstractController
{
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        if ($exception instanceof HttpException) {
            return JsonApiResponse::errors(
                $request,
                null,
                [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                ],
                [],
                $exception->getStatusCode()
            );
        }

        return JsonApiResponse::errors(
            $request,
            null,
            [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ],
            [],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
