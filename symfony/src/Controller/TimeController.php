<?php

namespace App\Controller;

use App\Response\JsonApiResponse;
use DateTime;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\RouterInterface;
use FOS\RestBundle\View\View;
use Swagger\Annotations as Swagger;
use FOS\RestBundle\Controller\Annotations as Rest;

class TimeController extends AbstractController
{
    use LoggerAwareTrait;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(LoggerInterface $logger, RouterInterface $router)
    {
        $this->setLogger($logger);
        $this->router = $router;
    }

    /**
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     *
     * @Rest\Get("/api/time", name="app.time.show")
     *
     * @Swagger\Parameter(
     *     in="header",
     *     name="Accept",
     *     required=true,
     *     type="string",
     *     default="application/json"
     * )
     *
     * @Swagger\Response(
     *     response=200,
     *     description="Returns the current server time",
     *     @Swagger\Schema(
     *         type="object",
     *         @Swagger\Property(
     *             property="data",
     *             type="object",
     *             @Swagger\Property(property="server_time", type="string", format="date-time")
     *         ),
     *         @Swagger\Property(
     *             property="links",
     *             type="object",
     *             @Swagger\Property(
     *                 property="self",
     *                 type="object",
     *                 @Swagger\Property(property="href", type="string"),
     *                 @Swagger\Property(
     *                     property="meta",
     *                     type="object",
     *                     @Swagger\Property(
     *                         property="methods",
     *                         type="array",
     *                         @Swagger\Items(type="string")
     *                     )
     *                 )
     *             ),
     *             @Swagger\Property(
     *                 property="log",
     *                 type="object",
     *                 @Swagger\Property(property="href", type="string"),
     *                 @Swagger\Property(
     *                     property="meta",
     *                     type="object",
     *                     @Swagger\Property(
     *                         property="methods",
     *                         type="array",
     *                         @Swagger\Items(type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function showAction(Request $request)
    {
        return JsonApiResponse::create(
            $request,
            $this->router,
            [
                'server_time' => (new DateTime())->format(DateTime::RFC3339),
            ],
            [],
            [
                'log' => 'app.time.log',
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return View
     *
     * @throws \Exception
     *
     * @Rest\Post("/api/time", name="app.time.log")
     *
     * @Swagger\Parameter(
     *     in="header",
     *     name="Accept",
     *     required=true,
     *     type="string",
     *     default="application/json"
     * )
     * @Swagger\Parameter(
     *     in="header",
     *     name="Content-Type",
     *     required=true,
     *     type="string",
     *     default="application/json"
     * )
     *
     * @Swagger\Parameter(
     *     in="body",
     *     name="date_time",
     *     required=true,
     *     @Swagger\Schema(
     *         type="object",
     *         @Swagger\Property(property="date_time", type="string", format="date-time")
     *     )
     * )
     *
     * @Swagger\Response(
     *     response=200,
     *     description="Logs and returns the posted client time",
     *     @Swagger\Schema(
     *         type="object",
     *         @Swagger\Property(
     *             property="data",
     *             type="object",
     *             @Swagger\Property(property="client_time", type="string", format="date-time")
     *         ),
     *         @Swagger\Property(
     *             property="links",
     *             type="object",
     *             @Swagger\Property(
     *                 property="self",
     *                 type="object",
     *                 @Swagger\Property(property="href", type="string"),
     *                 @Swagger\Property(
     *                     property="meta",
     *                     type="object",
     *                     @Swagger\Property(
     *                         property="methods",
     *                         type="array",
     *                         @Swagger\Items(type="string")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     *
     * @Swagger\Response(
     *     response=412,
     *     description="Invalid or missing content-type header or invalid JSON body",
     *     @Swagger\Schema(
     *         type="object",
     *         @Swagger\Property(
     *             property="errors",
     *             type="object",
     *             @Swagger\Property(property="message", type="string"),
     *             @Swagger\Property(property="code", type="int")
     *         )
     *     )
     * )
     *
     * @Swagger\Response(
     *     response=422,
     *     description="Invalid or missing date_time in body",
     *     @Swagger\Schema(
     *         type="object",
     *         @Swagger\Property(
     *             property="errors",
     *             type="object",
     *             @Swagger\Property(property="message", type="string"),
     *             @Swagger\Property(property="code", type="int")
     *         )
     *     )
     * )
     */
    public function logAction(Request $request)
    {
        if ($request->getContentType() !== 'json') {
            throw new PreconditionFailedHttpException('A "Content-Type" header with value "application/json" is required.');
        }

        $body = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new PreconditionFailedHttpException('Invalid JSON in body.');
        }

        if (empty($body['date_time'])) {
            throw new UnprocessableEntityHttpException('The "date_time" field is required.');
        }

        $clientDate = DateTime::createFromFormat(DateTime::RFC3339, $body['date_time']);

        if ($clientDate === false) {
            throw new UnprocessableEntityHttpException(sprintf('The "date_time" field must be passed in RFC3339 format "%s".', DateTime::RFC3339));
        }

        $this->logger->info(sprintf('client_time: %s', $clientDate->format(DateTime::RFC3339)));

        return JsonApiResponse::data(
            $request,
            $this->router,
            [
                'client_time' => $clientDate->format(DateTime::RFC3339),
            ],
            [],
            Response::HTTP_CREATED
        );
    }
}
