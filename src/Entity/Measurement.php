<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasurementRepository")
 * @ORM\Table(indexes={@ORM\Index(name="measured_at_idx",columns={"measured_at"})})
 * @ApiResource(
 *     attributes={
 *       "pagination_client_items_per_page"=true
 *     },
 *     collectionOperations={
 *       "get"={"pagination_maximum_items_per_page"=1000}
 *     },
 *     itemOperations={"get"},
 *     normalizationContext={"groups"={"measurement_read"}},
 *     denormalizationContext={"groups"={"measurement_write"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"mission.id": "exact", "sensor.id": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"measuredAt": "DESC"})
 */
class Measurement
{
    public const TYPE_MEASURED = 'measured';
    public const TYPE_ESTIMATED = 'estimated';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"measurement_read", "mission_log_entry_read", "mission"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mission")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"measurement_read", "mission"})
     */
    private $mission;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"measurement_read", "mission_log_entry_read", "mission"})
     */
    private $sensor;

    /**
     * @ORM\Column(type="datetime", precision=6)
     * @Groups({"measurement_read", "mission_log_entry_read", "mission"})
     */
    private $measuredAt;

    /**
     * @ORM\Column(type="json")
     * @Groups({"measurement_read", "mission"})
     */
    private $data = [];

    /**
     * @ORM\Column(type="json")
     */
    private $payload = [];

    /**
     * @ORM\Column(type="float")
     * @Groups({"measurement_read", "mission_log_entry_read", "mission"})
     */
    private $value;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"measurement_read", "mission_log_entry_read", "mission"})
     */
    private $type;

    /**
     * @Groups({"measurement_read", "mission_log_entry_read", "mission"})
     */
    public function getSensorName(): ?string
    {
        return $this->getMission()->getMissionSensorName($this->getSensor());
    }

    public function __construct()
    {
        $this->sensorWarnings = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getMission(): ?Mission
    {
        return $this->mission;
    }

    public function setMission(Mission $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    public function getSensor(): ?Sensor
    {
        return $this->sensor;
    }

    public function setSensor(Sensor $sensor): self
    {
        $this->sensor = $sensor;

        return $this;
    }

    public function getMeasuredAt(): ?\DateTimeInterface
    {
        return $this->measuredAt;
    }

    public function setMeasuredAt(\DateTimeInterface $measuredAt): self
    {
        $this->measuredAt = $measuredAt;

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

    public function getPayload(): ?array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type ?? static::TYPE_MEASURED;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
