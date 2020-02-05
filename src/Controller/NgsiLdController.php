<?php

namespace App\Controller;

use App\Scorpio\Client;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ngsi-ld/v1", name="ngsi_ld_")
 */
class NgsiLdController
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/entities/{id}", methods={"GET"}, name="entities")
     */
    public function entities(Request $request, string $id)
    {
        return $this->get($request, 'entities/'.$id);
    }

    /**
     * @Route("/{path}", requirements={"path"=".*"}, methods={"GET"}, name="index")
     */
    public function index(Request $request, string $path)
    {
        return $this->get($request, $path, ['query' => $request->query->all()]);
    }

    private function get(Request $request, string $path, array $options = [])
    {
        try {
            $options += [
                'query' => $request->query->all(),
            ];
            $response = $this->client->get('/ngsi-ld/v1/'.$path, $options);

            return new JsonResponse($response->toArray(), $response->getStatusCode());
        } catch (ClientException $exception) {
            return new JsonResponse([
                'type' => $exception->getCode(),
                'title' => $exception->getMessage(),
            ], $exception->getResponse()->getStatusCode());
        }
    }
}
