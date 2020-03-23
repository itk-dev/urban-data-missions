<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperimentLogEntryRepository")
 */
class ExperimentLogEntry
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"experiment"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"experiment"})
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experiment", inversedBy="logEntries")
     * @ORM\JoinColumn(nullable=false)
     */
    private $experiment;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"experiment"})
     */
    private $loggedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor")
     * @Groups({"experiment"})
     */
    private $sensor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"experiment"})
     */
    private $type;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
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

    public function getLoggedAt(): ?\DateTimeInterface
    {
        return $this->loggedAt;
    }

    public function setLoggedAt(\DateTimeInterface $loggedAt): self
    {
        $this->loggedAt = $loggedAt;

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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
