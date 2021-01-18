<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Scorpio\Client;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use whatwedo\SearchBundle\Annotation\Index;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SensorRepository")
 * @ApiResource()
 */
class Sensor
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     * @Groups({"sensor", "measurement_read", "mission_log_entry_read"})
     * @Index()
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Index()
     */
    private $type;

    /**
     * @ORM\Column(type="json")
     */
    private $data = [];

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $metadata;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $stream;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $streamId;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $streamObservation;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $streamObservationId;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $qoi;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $qoiId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Index()
     */
    private $name;

    public function __toString(): string
    {
        return $this->getId() ?? self::class;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getType(): ?string
    {
        if (null === $this->type && null !== $this->metadata) {
            $this->type = $this->metadata['type'] ?? null;
        }

        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @return Sensor
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getStream(): ?array
    {
        return $this->stream;
    }

    /**
     * @return Sensor
     */
    public function setStream(array $stream): self
    {
        $this->stream = $stream;
        $this->streamId = $stream['id'] ?? null;

        return $this;
    }

    public function getStreamObservation(): ?array
    {
        return $this->streamObservation;
    }

    /**
     * @return Sensor
     */
    public function setStreamObservation(array $streamObservation): self
    {
        $this->streamObservation = $streamObservation;
        $this->streamObservationId = $streamObservation['id'] ?? null;

        return $this;
    }

    public function getStreamObservationId(): ?string
    {
        return $this->streamObservationId;
    }

    public function getQoi(): ?array
    {
        return $this->qoi;
    }

    /**
     * @return Sensor
     */
    public function setQoi(array $qoi): self
    {
        $this->qoi = $qoi;
        $this->qoiId = $qoi['id'] ?? null;

        return $this;
    }

    public function getQoiId(): ?string
    {
        return $this->qoiId;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->data[Client::ENTITY_ATTRIBUTE_IDENTIFIER]['value'] ?? null;
    }

    public function getObservationType(): ?string
    {
        return $this->data[Client::ENTITY_ATTRIBUTE_OBSERVES]['object'] ?? null;
    }

    public function getSensorData(): array
    {
        return [
            'name' => $this->getName(),
            'identifier' => $this->getIdentifier(),
            'observation_type' => $this->getObservationType(),
            'qoi' => $this->getQoi(),
        ];
    }
}
