<?php

namespace App\Service;


use App\Entity\Event;
use App\Entity\Status;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

readonly class EventRegistrationManager
{

    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function subscribeCheck(Event $event, User $user): ?string
    {

        if ($event->getEventStatus()->getLabel() !== Status::OPEN) {
            return 'Registration is no longer open';
        }

        if ($event->getParticipantList()->contains($user)) {
            return 'Event already registered!';
        }

        if ($event->getRegistrationDeadline() < new \DateTime()) {
            return 'The registration deadline has passed!';
        }

        if ($event->getParticipantList()->count() >= $event->getNbMaxRegistrations()) {
            return 'There are maximum number of participants!';
        }

        $event->addParticipantList($user);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return null;
    }


    public
    function unsubscribeCheck(Event $event, User $user): ?string
    {
        if (!$event->getParticipantList()->contains($user)) {
            return 'You are not registered for this event.';
        }

        if ($event->getDateStartHour() < new \DateTime()) {
            return 'It is no longer possible to unsubscribe';
        }

        $event->removeParticipantList($user);
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return null;
    }

}
