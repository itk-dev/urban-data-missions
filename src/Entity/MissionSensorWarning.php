<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MissionSensorWarningRepository")
 */
class MissionSensorWarning
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MissionSensor", inversedBy="sensorWarnings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $missionSensor;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $min;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $max;

    /**
     * @ORM\Column(type="string", length=1024)
     */
    private $message;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMissionSensor(): ?MissionSensor
    {
        return $this->missionSensor;
    }

    public function setMissionSensor(MissionSensor $missionSensor): self
    {
        $this->missionSensor = $missionSensor;

        return $this;
    }

    public function getMin(): ?int
    {
        return $this->min;
    }

    public function setMin(?int $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function getMax(): ?int
    {
        return $this->max;
    }

    public function setMax(?int $max): self
    {
        $this->max = $max;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function __toString()
    {
        return static::class.'#'.$this->getId();
    }
}
