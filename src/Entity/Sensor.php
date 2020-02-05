<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SensorRepository")
 */
class Sensor implements TimestampableInterface
{
    use TimestampableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="string")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Experiment", mappedBy="sensors")
     */
    private $experiments;

    /**
     * @ORM\Column(type="json")
     */
    private $data = [];

    public function __construct()
    {
        $this->experiments = new ArrayCollection();
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
     * @return Collection|Experiment[]
     */
    public function getExperiments(): Collection
    {
        return $this->experiments;
    }

    public function addExperiment(Experiment $experiment): self
    {
        if (!$this->experiments->contains($experiment)) {
            $this->experiments[] = $experiment;
            $experiment->addSensor($this);
        }

        return $this;
    }

    public function removeExperiment(Experiment $experiment): self
    {
        if ($this->experiments->contains($experiment)) {
            $this->experiments->removeElement($experiment);
            $experiment->removeSensor($this);
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
