<?php

namespace App\Controller;

use App\Scorpio\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ngsi-ld")
 */
class NgsiLdController
{
    /**
     * @Route("/{path}", requirements={"path"=".*"}, methods={"GET"})
     */
    public function index(Request $request, string $path, Client $client)
    {
        try {
            $response = $client->get('/ngsi-ld/'.$path, ['query' => $request->query->all()]);

            return new JsonResponse($response->toArray(), $response->getStatusCode());
        } catch (ClientException $exception) {
            return new JsonResponse([
                'type' => $exception->getCode(),
                'title' => $exception->getMessage(),
            ], $exception->getResponse()->getStatusCode());
        }
    }
}
