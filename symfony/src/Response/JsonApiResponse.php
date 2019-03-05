<?php

namespace App\Response;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class JsonApiResponse
{
    public static function create(Request $request, ?RouterInterface $router, array $data = [], array $errors = [], array $links = [], int $status = 200, array $headers = []): View
    {
        $responseData = [];

        if (!empty($data)) {
            $responseData['data'] = $data;
        }

        if (!empty($errors)) {
            $responseData['errors'] = $errors;
        }

        if ($router instanceof RouterInterface) {
            $responseData['links'] = [
                'self' => [
                    'href' => $request->getUri(),
                    'meta' => [
                        'methods' => [$request->getRealMethod()],
                    ],
                ],
            ];

            if (!empty($links)) {
                foreach ($links as $key => $name) {
                    $responseData['links'][$key] = [
                        'href' => $router->generate($name, [], UrlGeneratorInterface::ABSOLUTE_URL),
                        'meta' => [
                            'methods' => $router->getRouteCollection()->get($name)->getMethods(),
                        ],
                    ];
                }
            }
        }

        return View::create($responseData, $status, $headers);
    }

    public static function data(Request $request, ?RouterInterface $router, array $data, array $links = [], int $status = 200, array $headers = []): View
    {
        return self::create($request, $router, $data, [], $links, $status, $headers);
    }

    public static function errors(Request $request, ?RouterInterface $router, array $errors, array $links = [], int $status = 200, array $headers = []): View
    {
        return self::create($request, $router, [], $errors, $links, $status, $headers);
    }
}
