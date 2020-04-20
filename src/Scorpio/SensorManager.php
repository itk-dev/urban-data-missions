<?php

namespace App\Scorpio;

use App\Entity\Sensor;
use App\Repository\SensorRepository;
use App\Traits\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SensorManager
{
    use LoggerTrait;

    /** @var Client */
    private $client;

    /** @var SensorRepository */
    private $sensorRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array */
    private $options;

    public function __construct(Client $client, SensorRepository $sensorRepository, EntityManagerInterface $entityManager, array $sensorManagerOptions)
    {
        $this->client = $client;
        $this->sensorRepository = $sensorRepository;
        $this->entityManager = $entityManager;

        $resolver = new OptionsResolver();
        $resolver
            ->setRequired('types')
            ->setAllowedTypes('types', 'string[]');

        $this->options = $resolver->resolve($sensorManagerOptions);
    }

    public function search(array $options = []): array
    {
        $query = ['q' => $options['query']['q'] ?? $options['query']['query'] ?? null];
        $result = [];
        foreach ($this->options['types'] as $type) {
            $brokerQuery = [
                'type' => $type,
                'idPattern' => $query['q'],
            ];
            try {
                // @see https://www.etsi.org/deliver/etsi_gs/CIM/001_099/009/01.01.01_60/gs_CIM009v010101p.pdf
                $response = $this->client->getEntities($brokerQuery);
                $items = array_filter($response->toArray());
                if (!empty($items)) {
                    $result[] = $items;
                }
            } catch (Exception $e) {
            }
        }

        if (!empty($result)) {
            // Flatten.
            $result = array_merge(...$result);
            // Index by id (to remove duplicates).
            $result = array_column($result, null, 'id');

            if (isset($options['mission_sensors'])) {
                $missionSensors = $options['mission_sensors'];
                foreach ($result as $id => &$item) {
                    $item['_metadata']['mission_sensor'] = $missionSensors[$id] ?? null;
                }
            }
        }

        return [
            'options' => $options,
            'data' => array_values($result),
        ];
    }

    public function getSensors(array $criteria = [])
    {
        return $this->getRepository()->findBy($criteria);
    }

    public function getSensor(string $id)
    {
        $sensor = $this->getRepository()->find($id);

        if (null === $sensor) {
            // @TODO: Get sensor data from broker.
            $data = ['type' => __METHOD__];
            if (!empty($data)) {
                $type = $data['type'] ?? null;

                $sensor = (new Sensor())
                    ->setId($id)
                    ->setType($type)
                    ->setData($data);

                $this->entityManager->persist($sensor);
            }
        }

        if (null === $sensor) {
            throw new \RuntimeException(sprintf('Invalid sensor id: %s', $id));
        }

        return $sensor;
    }

    public function updateSensors()
    {
        $repository = $this->getRepository();
        foreach ($this->options['types'] as $type) {
            $query = ['type' => $type];
            $response = $this->client->getEntities($query);
            if (Response::HTTP_OK === $response->getStatusCode()) {
                $result = $response->toArray();
                foreach ($result as $data) {
                    if (isset($data['id'])) {
                        $id = $data['id'];
                        $sensor = $repository->find($data['id']);
                        if (null === $sensor) {
                            $sensor = (new Sensor())
                                ->setId($id);
                        }
                        $sensor
                            ->setData($data);
                        $this->entityManager->persist($sensor);
                        $sensors[] = $sensor;
                    }
                }
                $this->entityManager->flush();
            }
        }
    }

    private function getRepository(): SensorRepository
    {
        return $this->entityManager->getRepository(Sensor::class);
    }
}
