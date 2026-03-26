<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Status;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class StatusManager
{
    public function __construct(
        private StatusRepository       $statusRepository,
        private EntityManagerInterface $entityManager
    )
    {
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

        $status = $this->statusRepository->findOneBy(['label' => $label]);
        if ($status) {
            $event->setEventStatus($status);
            $this->entityManager->persist($event);
        }
    }
}
