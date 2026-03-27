<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;


readonly class EventManager
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileUploader           $fileUploader
    )
    {
    }

    public function createEvent(Event $event, User $user, ?UploadedFile $imageFile = null): string
    {

        $event->setOrganizer($user);
        $event->setCampus($user->getCampus());
        $event->setDuration($event->getDateStartHour()->diff($event->getDateEndHour()));
        $event->addParticipantList($user);

        if ($imageFile) {
            $oldPhoto = $event->getPhoto();

            $newFilename = $this->fileUploader->upload($imageFile);
            $event->setPhoto($newFilename);

            if ($oldPhoto) {
                $this->fileUploader->delete($oldPhoto);
            }
        }


        $this->entityManager->persist($event);
        $this->entityManager->flush();

        return 'Event created';
    }
}
