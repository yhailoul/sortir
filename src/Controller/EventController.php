<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('events', name: 'events_')]
class EventController extends AbstractController
{
    #[Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, EventRepository $eventRepository): Response{
        $event = $eventRepository->find($id);
         if (!$event) {
             throw $this->createNotFoundException('Pas de event avec ce id '.$id);
         }


        return $this->render('event/detail.html.twig', [
            'event' => $event
        ]);
}

    #[Route('/create', name: 'event_create', methods: ['POST', 'GET'])]
    public function createEvent(
        EntityManagerInterface $entityManager,
        Request $request,
        FileUploader $fileUploader
    ): Response
    {
        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()){
//            /**
//             * @var UploadedFile $file
//             */
//            $file =$eventForm->get('backdrop')->getData();
//            if($file){
//                $event->setBackdrop(
//                    $fileUploader->upload($file, 'assets/images/backdrops', $event->getName())
//                );
//            }

////            $duration = $event->($dateEndHour-$dateStartHour);
//
//            $duration->setDuration();

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created!');

            return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);

        }

        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm->createView(),
        ]);
    }
}
