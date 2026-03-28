<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;


readonly class EventManager
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileUploader           $fileUploader,
        private StatusRepository       $statusRepository
    )
    {
    }

    public function createEvent(Event $event, User $user, ?UploadedFile $imageFile = null, string $action = 'save'): void
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

        $label = match ($action) {
            'publish' => 'Open',
            'cancel' => 'Canceled',
            'save' => 'In creation',
        };

        $status = $this->statusRepository->findOneBy(['label' => $label]);
        $event->setEventStatus($status);


        $this->entityManager->persist($event);
        $this->entityManager->flush();

    }
}
