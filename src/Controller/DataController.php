<?php

namespace App\Controller;

use App\IoTCrawler\SearchEnabler\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\MimeTypesInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api/data", name="api_data_")
 */
class DataController extends AbstractController
{
    /**
     * @Route(
     *     "/observation_types.{_format}",
     *     name="observation_types",
     *     format="json",
     *     requirements={
     *         "_format": "json",
     *     })
     */
    public function getObservationTypes(Request $request, Client $client, string $_format, SerializerInterface $serializer, MimeTypesInterface $mimeTypes)
    {
        $properties = $client->getObservableProperties();

        $data = array_map(static function (object $item) {
            return [
                'label' => $item->id,
                'value' => $item->alternativeType,
            ];
        }, $properties->observableProperties ?? []);
        $response = new Response($serializer->serialize($data, $_format));

        $mimeType = $mimeTypes->getMimeTypes($_format);
        if (!empty($mimeType)) {
            $response->headers->set('content-type', reset($mimeType));
        }

        return $response;
    }
}
