<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[Assert\Callback('validateDates')]
#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTime $dateStartHour = null;

    #[ORM\Column]
    private ?\DateInterval $duration = null;

    #[ORM\Column]
    private ?\DateTime $dateEndHour = null;

    #[ORM\Column]
    private ?\DateTime $registrationDeadline = null;

    #[ORM\Column]
    private ?int $nbMaxRegistrations = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $infosEvent = null;

    #[ORM\ManyToOne(inversedBy: 'organizerEvents')]
    private User $organizer;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'eventInscription')]
    private Collection $participantList;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $eventLocation = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $eventStatus = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;



    public function __construct()
    {
        $this->participantList = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDateStartHour(): ?\DateTime
    {
        return $this->dateStartHour;
    }

    public function setDateStartHour(\DateTime $dateStartHour): static
    {
        $this->dateStartHour = $dateStartHour;

        return $this;
    }

    public function getDuration(): ?\DateInterval
    {
        return $this->duration;
    }

    public function setDuration(\DateInterval $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDateEndHour(): ?\DateTime
    {
        return $this->dateEndHour;
    }

    public function setDateEndHour(\DateTime $dateEndHour): static
    {
        $this->dateEndHour = $dateEndHour;

        return $this;
    }

    public function getRegistrationDeadline(): ?\DateTime
    {
        return $this->registrationDeadline;
    }

    public function setRegistrationDeadline(\DateTime $registrationDeadline): static
    {
        $this->registrationDeadline = $registrationDeadline;

        return $this;
    }

    public function getNbMaxRegistrations(): ?int
    {
        return $this->nbMaxRegistrations;
    }

    public function setNbMaxRegistrations(int $nbMaxRegistrations): static
    {
        $this->nbMaxRegistrations = $nbMaxRegistrations;

        return $this;
    }

    public function getInfosEvent(): ?string
    {
        return $this->infosEvent;
    }

    public function setInfosEvent(string $infosEvent): static
    {
        $this->infosEvent = $infosEvent;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getParticipantList(): Collection
    {
        return $this->participantList;
    }

    public function addParticipantList(User $participantList): static
    {
        if (!$this->participantList->contains($participantList)) {
            $this->participantList->add($participantList);
            $participantList->addEventInscription($this);
        }

        return $this;
    }

    public function removeParticipantList(User $participantList): static
    {
        if ($this->participantList->removeElement($participantList)) {
            $participantList->removeEventInscription($this);
        }

        return $this;
    }

    public function getEventLocation(): ?Location
    {
        return $this->eventLocation;
    }

    public function setEventLocation(?Location $eventLocation): static
    {
        $this->eventLocation = $eventLocation;

        return $this;
    }

    public function getEventStatus(): ?Status
    {
        return $this->eventStatus;
    }

    public function setEventStatus(?Status $eventStatus): static
    {
        $this->eventStatus = $eventStatus;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->getDateStartHour() && $this->getDateEndHour()) {
            if ($this->getDateEndHour() <= $this->getDateStartHour()) {
                $context->buildViolation('The end date must be later than the start date.')
                    ->atPath('dateEndHour')
                    ->addViolation();
            }
        }

        if ($this->getRegistrationDeadline() && $this->getDateStartHour()) {
            if ($this->getRegistrationDeadline() >= $this->getDateStartHour()) {
                $context->buildViolation('The registration deadline must be before the start date.')
                    ->atPath('registrationDeadline')
                    ->addViolation();
            }
        }

        if ($this->getDateStartHour() && $this->getDateStartHour() < new \DateTime()) {
            $context->buildViolation('The start date cannot be in the past.')
                ->atPath('dateStartHour')
                ->addViolation();
        }
    }
}
