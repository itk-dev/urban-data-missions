<?php

namespace App\Scorpio;

use App\Entity\Sensor;
use App\Repository\SensorRepository;
use App\Traits\LoggerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SensorManager
{
    use LoggerTrait;

    /** @var Client */
    private $client;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array */
    private $options;

    public function __construct(Client $client, EntityManagerInterface $entityManager, array $sensorManagerOptions)
    {
        $this->client = $client;
        $this->entityManager = $entityManager;

        $resolver = new OptionsResolver();
        $resolver
            ->setRequired('types')
            ->setAllowedTypes('types', 'string[]');

        $this->options = $resolver->resolve($sensorManagerOptions);
    }

    public function getSensors(array $criteria = [])
    {
        return $this->getRepository()->findBy($criteria);
    }

    public function getSensor(string $id)
    {
        return $this->getRepository()->find($id);
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
