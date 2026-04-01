<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Status;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

class StatusManager
{
    private array $statusTab;

    public function __construct(
        private readonly StatusRepository       $statusRepository,
        private readonly EntityManagerInterface $entityManager,

    )
    {
        //Récupère tous les status une seule fois
        $allStatus = $this->statusRepository->findAll();
        foreach ($allStatus as $e) {
            //Stocke l'objet avec le label en clé
            $this->statusTab[$e->getLabel()] = $e;
        }
    }

    public function updateEventStatus(Event $event): void
    {
        $now = new \DateTime;

        if ($event->getDateEndHour() < (clone $now)->modify('-30 days')) {
            $label = Status::HISTORIZED;
        } elseif ($event->getDateEndHour() <= $now) {
            $label = Status::ENDED;
        } elseif ($event->getDateStartHour() <= $now) {
            $label = Status::IN_PROGRESS;
        } elseif ($event->getRegistrationDeadline() <= $now) {
            $label = Status::CLOSED;
        } else {
            $label = Status::OPEN;
        }

        $status = $this->statusTab[$label];
        if ($status) {
            $event->setEventStatus($status);
            $this->entityManager->persist($event);
        }
    }

    public function cancelEvent(Event $event): void
    {

        $cancelStatus = $this->statusTab['Canceled'];

        $event->setEventStatus($cancelStatus);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

    }
}
