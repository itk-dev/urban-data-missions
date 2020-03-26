<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ExperimentLogEntryRepository")
 * @ApiResource(
 *     collectionOperations={"GET", "POST"},
 *     itemOperations={"GET", "PATCH"},
 *     normalizationContext={"groups"={"experiment_log_entry_read"}},
 *     denormalizationContext={"groups"={"experiment_log_entry_write"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"experiment.id": "exact", "type": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"loggedAt": "DESC"})
 */
class ExperimentLogEntry
{
    public const TYPE_USER = 'user';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_ALERT = 'alert';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"experiment_log_entry_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"experiment_log_entry_read", "experiment_log_entry_write"})
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experiment", inversedBy="logEntries")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"experiment_log_entry_read", "experiment_log_entry_write"})
     * @Assert\NotNull()
     */
    private $experiment;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"experiment_log_entry_read", "experiment_log_entry_write"})
     * @Assert\NotNull()
     */
    private $loggedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor")
     * @Groups({"experiment_log_entry_read", "experiment_log_entry_write"})
     */
    private $sensor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"experiment_log_entry_read", "experiment_log_entry_write"})
     * @Assert\NotBlank()
     */
    private $type = self::TYPE_USER;

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
