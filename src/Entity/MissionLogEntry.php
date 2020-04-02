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
 * @ORM\Entity(repositoryClass="App\Repository\MissionLogEntryRepository")
 * @ApiResource(
 *     collectionOperations={"GET", "POST"},
 *     itemOperations={"GET", "PATCH"},
 *     normalizationContext={"groups"={"mission_log_entry_read"}},
 *     denormalizationContext={"groups"={"mission_log_entry_write"}}
 * )
 * @ApiFilter(SearchFilter::class, properties={"mission.id": "exact", "type": "exact"})
 * @ApiFilter(OrderFilter::class, properties={"loggedAt": "DESC"})
 */
class MissionLogEntry
{
    public const TYPE_USER = 'user';
    public const TYPE_SYSTEM = 'system';
    public const TYPE_ALERT = 'alert';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"mission_log_entry_read"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"mission_log_entry_read", "mission_log_entry_write"})
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Mission")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"mission_log_entry_read", "mission_log_entry_write"})
     * @Assert\NotNull()
     */
    private $mission;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"mission_log_entry_read", "mission_log_entry_write"})
     * @Assert\NotNull()
     */
    private $loggedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sensor")
     * @Groups({"mission_log_entry_read", "mission_log_entry_write"})
     */
    private $sensor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"mission_log_entry_read", "mission_log_entry_write"})
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

    public function getMission(): ?Mission
    {
        return $this->mission;
    }

    public function setMission(Mission $mission): self
    {
        $this->mission = $mission;

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
