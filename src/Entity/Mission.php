<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
     * @Assert\NotBlank()
     */
    private $title;

    /**
     * @ORM\Column(type="text")
     * @Groups({"mission_read", "log_entry"})
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"mission_read", "log_entry"})
     * @Assert\NotBlank()
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
     * @Groups({"mission_read"})
     */
    private $finishedAt;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $subscription;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MissionSensor", mappedBy="mission", cascade={"persist"}, orphanRemoval=true)
     */
    private $missionSensors;

    public function __construct()
    {
        $this->missionSensors = new ArrayCollection();
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

    public function getSubscription(): ?array
    {
        return $this->subscription;
    }

    public function setSubscription(?array $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    public function __toString()
    {
        return $this->getTitle() ?? static::class;
    }

    /**
     * @return Collection|MissionSensor[]
     */
    public function getMissionSensors(): Collection
    {
        return $this->missionSensors;
    }

    /** @var array Map from sensor id to sensor name */
    private $sensorNames;

    public function getMissionSensorNames(): array
    {
        if (null === $this->sensorNames) {
            $this->sensorNames = [];

            foreach ($this->getMissionSensors() as $missionSensor) {
                $this->sensorNames[$missionSensor->getSensor()->getId()] = $missionSensor->getName();
            }
        }

        return $this->sensorNames;
    }

    public function getMissionSensorIds(): array
    {
        return array_keys($this->getMissionSensorNames());
    }

    public function getMissionSensorName(Sensor $sensor): ?string
    {
        return $this->getMissionSensorNames()[$sensor->getId()] ?? null;
    }

    public function addMissionSensor(MissionSensor $missionSensor): self
    {
        if (!$this->missionSensors->contains($missionSensor)) {
            $this->missionSensors[] = $missionSensor;
            $missionSensor->setMission($this);
        }

        return $this;
    }

    public function removeMissionSensor(MissionSensor $missionSensor): self
    {
        if ($this->missionSensors->contains($missionSensor)) {
            $this->missionSensors->removeElement($missionSensor);
            // set the owning side to null (unless already changed)
            if ($missionSensor->getMission() === $this) {
                $missionSensor->setMission(null);
            }
        }

        return $this;
    }
}
