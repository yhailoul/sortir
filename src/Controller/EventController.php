<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\Location;
use App\Form\CancelledEventType;
use App\Form\EventType;
use App\Form\FilterSearchType;
use App\Form\LocationType;
use App\Form\Model\FilterSearch;
use App\Repository\EventRepository;
use App\Repository\LocationRepository;
use App\Service\EventManager;
use App\Service\EventRegistrationManager;
use App\Service\StatusManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('events', name: 'events_')]
class EventController extends AbstractController
{

    #[Route('/list', name: 'listFilters', methods: ['GET', 'POST'])]
    public function listFilters(Request          $request,
                                EventRepository  $eventRepository,
                                Security         $security,
                                StatusManager $statusManager,
                                ): Response
    {
        $events = [];
        $eventsAll = $eventRepository->AllEvents();
       // $eventList = $eventRepository->findEventsToUpdate();
        $user = $security->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('utilisateur inexistant');
        }

//        foreach ($eventList as $e) {
//            $statusManager->updateEventStatus($e);
//        }
//        $entityManager->flush();

        $eventSearch = new FilterSearch();
        $filterForm = $this->createForm(FilterSearchType::class, $eventSearch);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            $campus = $eventSearch->getCampus();
            $events = $eventRepository->filterBySelection($user, $eventSearch, $campus);

        }
        return $this->render('event/list.html.twig', [
            'events' => $events,
            'eventAll'=>$eventsAll,
//            'eventList' => $eventList,
            'filterForm' => $filterForm,
            'user' => $user
        ]);

    }

    #[Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(int $id, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->find($id);
        if (!$event) {
            throw $this->createNotFoundException('Not event with the id ' . $id);
        }


        return $this->render('event/detail.html.twig', [
            'event' => $event
        ]);
    }

    #[Route('/create', name: 'create', methods: ['POST', 'GET'])]
    #[IsGranted("ROLE_USER")]
    public function createEvent(
        Request      $request,
        EventManager $eventManager
    ): Response
    {
        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()) {
            $buttonClicked = $eventForm->getClickedButton();
            $action = $buttonClicked->getName() ?? 'save'; // Renvoie save par défaut si null pour éviter l'erreur
            $imageFile = $eventForm->get('eventPhoto')->getData();
            $eventManager->createEvent($event, $this->getUser(), $imageFile, $action);

            $this->addFlash('success', 'Event created!');

            return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm->createView(),
        ]);
    }

    #[Route('/new/location', name: 'location_create', methods: ['GET','POST'])]
    public function createNewEventLocation(Request $request, EntityManagerInterface $manager, LocationRepository $locationRepository ): Response{
        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location);
        $locationForm->handleRequest($request);
        $isExistingLocation = $locationRepository->findBy(array('name'=>$location->getName()));
        if($isExistingLocation){
            $this->addFlash('error', 'Location already exists!');
            return $this->redirectToRoute('events_create');
        }
        if($locationForm->isSubmitted() && $locationForm->isValid()){
            $location->setLatitude(null);
            $location->setLongitude(null);
            $manager->persist($location);
            $manager->flush();
            $this->addFlash('success', 'Location created!');
            return $this->redirectToRoute('events_create');
        }
        return $this->render('event/newLocation.html.twig', [
            'locationForm' => $locationForm
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function editEvent(
        int             $id,
        EventRepository $eventRepository,
        Request         $request,
        EventManager    $eventManager
    ): Response
    {

        $event = $eventRepository->find($id);

        if (!$this->isGranted('EVENT_EDIT', $event)) {
            $this->addFlash('danger', 'You do not have permission to modify this event.');
            return $this->redirectToRoute('events_listFilters');
        }

        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);
        if ($eventForm->isSubmitted() && $eventForm->isValid()) {
            $buttonClicked = $eventForm->getClickedButton();
            $action = $buttonClicked->getName() ?? 'save'; // Renvoie save par défaut si null pour éviter l'erreur
            $imageFile = $eventForm->get('eventPhoto')->getData();
            $eventManager->createEvent($event, $this->getUser(), $imageFile, $action);
            $this->addFlash('success', 'Event edited!');

            return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);
        }
        return $this->render('event/edit.html.twig', [
            'eventFormEdit' => $eventForm->createView()
        ]);
    }

    #[Route('/cancel/{id}', name: 'cancelled', requirements: ['id' => '\d+'])]
    #[IsGranted("ROLE_ADMIN")]
    public function cancelEvent(
        int             $id,
        EventRepository $eventRepository,
        Request         $request,
        EntityManagerInterface $manager
    ): Response
    {

        $event = $eventRepository->find($id);
        $form = $this->createForm(CancelledEventType::class, $event);
        $form->handleRequest($request);
        if($event->getEventStatus()=="canceled"){
            $this->addFlash('danger', 'Event cancelled!');
            return $this->redirectToRoute('events_listFilters');
        }else{
            $eventCancelledStatus=$event->getEventStatus()->setLabel("canceled");
            $event->setEventStatus($eventCancelledStatus);

            if ($form->isSubmitted()) {
                $manager->persist($event);
                $manager->flush();

                $this->addFlash('success', 'Event cancelled');
                return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);
            }
        }

        return $this->render('event/cancelledEvent.html.twig', [
            'eventFormCancelled' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
    public function deleteEvent(
        int                    $id,
        EventRepository        $eventRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $event = $eventRepository->find($id);

        if (!$event) {
            throw $this->createNotFoundException('There is not event with the id ' . $id);
        }

        $entityManager->remove($event);
        $entityManager->flush();

        $this->addFlash('success', 'Event deleted!');
        return $this->redirectToRoute('events_list', ['page' => 1]);
    }

    #[Route('/{id}/register', name: 'register', requirements: ['id' => '\d+'])]
    public function registerEvent(Event $event, EventRegistrationManager $eventRegistrationMananger): Response
    {
        $user = $this->getUser();

        //Récupère soit le texte de l'erreur soit null si tout s'est bien passé
        $error = $eventRegistrationMananger->subscribeCheck($event, $user);

        //Si erreur
        if ($error) {
            $this->addFlash('danger', $error);
        } else {
            $this->addFlash('success', 'Event registered!');

        }
        return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);
    }

    #[Route('/{id}/unsubscribe', name: 'unsubscribe', requirements: ['id' => '\d+'])]
    public function unsubscribeEvent(Event $event, EventRegistrationManager $eventRegistrationMananger): Response
    {
        $user = $this->getUser();

        //Récupère soit le texte de l'erreur soit null si tout s'est bien passé
        $error = $eventRegistrationMananger->unsubscribeCheck($event, $user);

        //Si erreur
        if ($error) {
            $this->addFlash('danger', $error);
        } else {
            $this->addFlash('success', 'You have been unsubscribed from the event !');

        }
        return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);
    }
}
