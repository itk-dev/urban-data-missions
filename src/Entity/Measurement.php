<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeasurementRepository")
 */
class Measurement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experiment", inversedBy="measurements")
     * @ORM\JoinColumn(nullable=false)
     */
    private $experiment;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $sensor;

    /**
     * @ORM\Column(type="datetime", precision=6)
     */
    private $measuredAt;

    /**
     * @ORM\Column(type="json")
     */
    private $data = [];

    /**
     * @ORM\Column(type="json")
     */
    private $payload = [];

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    public function getId(): ?int
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

    public function getSensor(): ?string
    {
        return $this->sensor;
    }

    public function setSensor(string $sensor): self
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
