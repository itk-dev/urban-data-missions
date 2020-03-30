<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MissionRepository")
 * @ApiResource(
 *     normalizationContext={"groups"={"mission_read"}}
 * )
 */
class Mission
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     * @ORM\Column(type="guid")
     * @Groups({"mission_read", "log_entry"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mission_read", "log_entry"})
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"mission_read", "log_entry"})
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mission_read", "log_entry"})
     */
    private $location;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"mission_read", "log_entry"})
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"mission_read", "log_entry"})
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MissionTheme", inversedBy="missions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"mission_read", "log_entry"})
     */
    private $theme;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $finishedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subscription;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Sensor")
     */
    private $sensors;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SensorWarning", mappedBy="mission", orphanRemoval=true)
     */
    private $sensorWarnings;

    public function __construct()
    {
        $this->sensors = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

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

    public function getTheme(): ?MissionTheme
    {
        return $this->theme;
    }

    public function setTheme(?MissionTheme $theme): self
    {
        $this->theme = $theme;

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
            $sensorWarning->setMission($this);
        }

        return $this;
    }

    public function removeSensorWarning(SensorWarning $sensorWarning): self
    {
        if ($this->sensorWarnings->contains($sensorWarning)) {
            $this->sensorWarnings->removeElement($sensorWarning);
            // set the owning side to null (unless already changed)
            if ($sensorWarning->getMission() === $this) {
                $sensorWarning->setMission(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle();
    }
}
