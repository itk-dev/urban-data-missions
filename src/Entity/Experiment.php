<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperimentRepository")
 * @ApiResource(
 *     normalizationContext={"groups"={"experiment_read"}}
 * )
 */
class Experiment implements BlameableInterface, TimestampableInterface
{
    use BlameableTrait;
    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"experiment_read", "log_entry"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"experiment_read", "log_entry"})
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subscription;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Measurement", mappedBy="experiment", orphanRemoval=true)
     * @ORM\OrderBy({"measuredAt": "DESC"})
     * @ApiSubresource()
     */
    private $measurements;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Sensor", inversedBy="experiments")
     */
    private $sensors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ExperimentLogEntry", mappedBy="experiment", orphanRemoval=true)
     * @ORM\OrderBy({"loggedAt": "DESC"})
     * @ApiSubresource()
     */
    private $logEntries;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finishedAt;

    /**
     * @ORM\Column(type="float")
     */
    private $latitude;

    /**
     * @ORM\Column(type="float")
     */
    private $longitude;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SensorWarning", mappedBy="experiment", orphanRemoval=true)
     */
    private $sensorWarnings;

    public function __construct()
    {
        $this->measurements = new ArrayCollection();
        $this->sensors = new ArrayCollection();
        $this->logEntries = new ArrayCollection();
        $this->sensorWarnings = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSubscription(): ?string
    {
        return $this->subscription;
    }

    public function setSubscription(?string $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * @return Collection|Measurement[]
     */
    public function getMeasurements(): Collection
    {
        return $this->measurements;
    }

    public function addMeasurement(Measurement $measurement): self
    {
        if (!$this->measurements->contains($measurement)) {
            $this->measurements[] = $measurement;
            $measurement->setExperiment($this);
        }

        return $this;
    }

    public function removeMeasurement(Measurement $measurement): self
    {
        if ($this->measurements->contains($measurement)) {
            $this->measurements->removeElement($measurement);
            // set the owning side to null (unless already changed)
            if ($measurement->getExperiment() === $this) {
                $measurement->setExperiment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sensor[]
     */
    public function getSensors(): Collection
    {
        return $this->sensors;
    }

    public function addSensor(Sensor $sensor): self
    {
        if (!$this->sensors->contains($sensor)) {
            $this->sensors[] = $sensor;
        }

        return $this;
    }

    public function removeSensor(Sensor $sensor): self
    {
        if ($this->sensors->contains($sensor)) {
            $this->sensors->removeElement($sensor);
        }

        return $this;
    }

    /**
     * @return Collection|ExperimentLogEntry[]
     */
    public function getLogEntries(): Collection
    {
        return $this->logEntries;
    }

    public function addLogEntry(ExperimentLogEntry $logEntry): self
    {
        if (!$this->logEntries->contains($logEntry)) {
            $this->logEntries[] = $logEntry;
            $logEntry->setExperiment($this);
        }

        return $this;
    }

    public function removeLogEntry(ExperimentLogEntry $logEntry): self
    {
        if ($this->logEntries->contains($logEntry)) {
            $this->logEntries->removeElement($logEntry);
            // set the owning side to null (unless already changed)
            if ($logEntry->getExperiment() === $this) {
                $logEntry->setExperiment(null);
            }
        }

        return $this;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

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
            $sensorWarning->setExperiment($this);
        }

        return $this;
    }

    public function removeSensorWarning(SensorWarning $sensorWarning): self
    {
        if ($this->sensorWarnings->contains($sensorWarning)) {
            $this->sensorWarnings->removeElement($sensorWarning);
            // set the owning side to null (unless already changed)
            if ($sensorWarning->getExperiment() === $this) {
                $sensorWarning->setExperiment(null);
            }
        }

        return $this;
    }
}
