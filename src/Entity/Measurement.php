<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasurementRepository")
 */
class Measurement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"measurement", "experiment"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experiment", inversedBy="measurements")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"measurement", "experiment"})
     */
    private $experiment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"measurement", "experiment"})
     */
    private $sensor;

    /**
     * @ORM\Column(type="datetime", precision=6)
     * @Groups({"measurement", "experiment"})
     */
    private $measuredAt;

    /**
     * @ORM\Column(type="json")
     * @Groups({"measurement", "experiment"})
     */
    private $data = [];

    /**
     * @ORM\Column(type="json")
     */
    private $payload = [];

    /**
     * @ORM\Column(type="float")
     * @Groups({"measurement", "experiment"})
     */
    private $value;

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
}
