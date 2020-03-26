<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasurementRepository")
 * @ApiResource(
 *     paginationClientEnabled=true,
 *     paginationClientItemsPerPage=true,
 *     collectionOperations={"GET"},
 *     itemOperations={"GET"},
 *     normalizationContext={"groups"={"measurement_read"}},
 *     denormalizationContext={"groups"={"measurement_write"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"experiment.id": "exact", "sensor.id": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"measuredAt": "DESC"})
 */
class Measurement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"measurement_read", "experiment"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experiment", inversedBy="measurements")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"measurement_read", "experiment"})
     */
    private $experiment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"measurement_read", "experiment"})
     */
    private $sensor;

    /**
     * @ORM\Column(type="datetime", precision=6)
     * @Groups({"measurement_read", "experiment"})
     */
    private $measuredAt;

    /**
     * @ORM\Column(type="json")
     * @Groups({"measurement_read", "experiment"})
     */
    private $data = [];

    /**
     * @ORM\Column(type="json")
     */
    private $payload = [];

    /**
     * @ORM\Column(type="float")
     * @Groups({"measurement_read", "experiment"})
     */
    private $value;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SensorWarning", mappedBy="measurement", orphanRemoval=true)
     */
    private $sensorWarnings;

    public function __construct()
    {
        $this->sensorWarnings = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getExperiment(): ?Experiment
    {
        return $this->experiment;
    }

    public function setExperiment(?Experiment $experiment): self
    {
        $this->experiment = $experiment;

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

    /**
     * @return Collection|SensorWarning[]
     */
    public function getSensorWarnings(): Collection
    {
        return $this->sensorWarnings;
    }

    public function addSensorWarning(SensorWarning $sensorWarning): self
    {
        if (!$this->sensorWarnings->contains($sensorWarning)) {
            $this->sensorWarnings[] = $sensorWarning;
            $sensorWarning->setMeasurement($this);
        }

        return $this;
    }

    public function removeSensorWarning(SensorWarning $sensorWarning): self
    {
        if ($this->sensorWarnings->contains($sensorWarning)) {
            $this->sensorWarnings->removeElement($sensorWarning);
            // set the owning side to null (unless already changed)
            if ($sensorWarning->getMeasurement() === $this) {
                $sensorWarning->setMeasurement(null);
            }
        }

        return $this;
    }
}
