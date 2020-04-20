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

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"sensor", "measurement_read", "mission_log_entry_read"})
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Mission", mappedBy="sensors")
     */
    private $missions;

    /**
     * @ORM\Column(type="json")
     */
    private $data = [];

    public function __construct()
    {
        $this->missions = new ArrayCollection();
        $this->sensorWarnings = new ArrayCollection();
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
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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

        if (isset($data['type'])) {
            $this->setType($data['type']);
        }

        return $this;
    }

    public function __toString()
    {
        return $this->getId() ?? self::class;
    }
}
