<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Form\FilterSearchType;
use App\Form\ListSortingType;
use App\Form\Model\FilterSearch;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('events', name: 'events_')]
class EventController extends AbstractController
{
    #[Route('/list', name: 'list', methods: ['GET','POST'])]
    public function list(Request $request, EventRepository $eventRepository, Security $security): Response
    {   $events=[];
        $eventList = $eventRepository->findAll();
        $user = $security->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('utilisateur inexistant');
        }
        $eventSearch = new FilterSearch();
        $filterForm = $this->createForm(FilterSearchType::class, $eventSearch);
        $filterForm->handleRequest($request);

         if ($filterForm->isSubmitted()) {

           $events = $eventRepository->filterBySelection($user, $eventSearch);
            //dd($events);
        }
        return $this->render('event/list.html.twig', [
            'events' => $events,
            'eventList' => $eventList,
            'filterForm' => $filterForm,
            'user' => $user,
        ]);

    }

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

    #[Route('/create', name: 'create', methods: ['POST', 'GET'])]
    public function createEvent(
        EntityManagerInterface $entityManager,
        Request $request,
        FileUploader $fileUploader
    ): Response
    {
        $event = new Event();
        $eventForm = $this->createForm(Event::class, $event);

        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()){
            /**
             * @var UploadedFile $file
             */
            $file =$eventForm->get('backdrop')->getData();
            if($file){
                $event->setBackdrop(
                    $fileUploader->upload($file, 'assets/images/backdrops', $event->getName())
                );
            }

            $duration = $event->getDateStartHour()->diff($event->getDateEndHour());
            $event->setDuration($duration);

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created!');

            return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);

        }

        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm->createView(),
        ]);
    }


    #[ROUTE('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function editEvent(
        int $id,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager,
        Request $request,
        FileUploader $fileUploader
    ): Response
    {
        $event = $eventRepository->find($id);

        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);
        if ($eventForm->isSubmitted() && $eventForm->isValid()){
            $duration = $event->getDateStartHour()->diff($event->getDateEndHour());
            $event->setDuration($duration);
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Event edited!');

            return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);
        }
        return $this->render('event/edit.html.twig', [
            'eventFormEdit' => $eventForm->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function deleteEvent(
        int $id,
        EventRepository $eventRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('There is not event with the id '.$id);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'Event deleted!');
        return $this->redirectToRoute('events_list', ['page' => 1]);
    }
}
