<?php

namespace App\Controller;

use App\Scorpio\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/ngsi-ld/v1", name="ngsi_ld_")
 */
class NgsiLdController extends AbstractController
{
    /** @var Client */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @Route("/{path}", requirements={"path"=".*"}, methods={"GET"}, name="index")
     */
    public function index(Request $request, string $path)
    {
        try {
            $options = ['query' => $request->query->all()];
            $response = $this->client->get('/ngsi-ld/v1/'.$path, $options);
            $data = $response->toArray();

            array_walk_recursive($data, function (&$value) {
                if (is_string($value) && 0 === strpos($value, 'urn:ngsi-ld:')) {
                    $value = $this->generateUrl('ngsi_ld_index', ['path' => 'entities/'.$value], UrlGeneratorInterface::ABSOLUTE_URL);
                }
            });

            return new JsonResponse($data, $response->getStatusCode());
        } catch (ClientException $exception) {
            return new JsonResponse([
                'type' => $exception->getCode(),
                'title' => $exception->getMessage(),
            ], $exception->getResponse()->getStatusCode());
        }
    }
}
