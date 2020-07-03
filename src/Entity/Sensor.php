<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

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
     */
    private $id;

    /** @var string|null */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Mission", mappedBy="sensors")
     */
    private $missions;

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

    public function __construct()
    {
        $this->missions = new ArrayCollection();
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

    /**
     * @return Collection|Mission[]
     */
    public function getMissions(): Collection
    {
        return $this->missions;
    }

    public function addMission(Mission $mission): self
    {
        if (!$this->missions->contains($mission)) {
            $this->missions[] = $mission;
            $mission->addSensor($this);
        }

        return $this;
    }

    public function removeMission(Mission $mission): self
    {
        if ($this->missions->contains($mission)) {
            $this->missions->removeElement($mission);
            $mission->removeSensor($this);
        }

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

    public function __toString()
    {
        return $this->getId() ?? self::class;
    }
}
