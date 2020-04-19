<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass="App\Repository\MissionSensorRepository")
 */
class MissionSensor
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mission", inversedBy="missionSensors")
     * @ORM\JoinColumn(nullable=false)
     */
    private $mission;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sensor;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MissionSensorWarning", mappedBy="missionSensor", cascade={"persist"}, orphanRemoval=true)
     */
    private $sensorWarnings;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled = true;

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

    public function setMission(?Mission $mission): self
    {
        $this->mission = $mission;

        return $this;
    }

    public function getSensor(): ?Sensor
    {
        return $this->sensor;
    }

    public function setSensor(?Sensor $sensor): self
    {
        $this->sensor = $sensor;

        return $this;
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

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Collection|MissionSensorWarning[]
     */
    public function getSensorWarnings(): Collection
    {
        return $this->sensorWarnings;
    }

    public function addSensorWarning(MissionSensorWarning $sensorWarning): self
    {
        if (!$this->sensorWarnings->contains($sensorWarning)) {
            $this->sensorWarnings[] = $sensorWarning;
            $sensorWarning->setMissionSensor($this);
        }

        return $this;
    }

    public function removeSensorWarning(MissionSensorWarning $sensorWarning): self
    {
        if ($this->sensorWarnings->contains($sensorWarning)) {
            $this->sensorWarnings->removeElement($sensorWarning);
            // set the owning side to null (unless already changed)
            if ($sensorWarning->getMissionSensor() === $this) {
                $sensorWarning->setMissionSensor(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return sprintf('%s (%s)',
            $this->getSensor() ? $this->getSensor()->getId() : '',
            $this->getName()
        );
    }
}
