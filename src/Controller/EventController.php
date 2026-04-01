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
use App\Service\EventManager;
use App\Service\EventRegistrationManager;
use App\Service\LocationManager;
use App\Service\StatusManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('events', name: 'events_')]
class EventController extends AbstractController
{

    public function __construct(private readonly LocationManager $locationManager)
    {
    }

    #[Route('/list', name: 'listFilters', methods: ['GET', 'POST'])]
    public function listFilters(Request         $request,
                                EventRepository $eventRepository,
                                Security        $security,
                                StatusManager   $statusManager,
    ): Response
    {
        $events = [];
        $eventsAll = $eventRepository->AllEvents();
        $user = $security->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('utilisateur inexistant');
        }

        $eventSearch = new FilterSearch();
        $filterForm = $this->createForm(FilterSearchType::class, $eventSearch);
        $filterForm->handleRequest($request);

        $isFiltered = false;

        if ($filterForm->isSubmitted()) {
            $campus = $eventSearch->getCampus();
            $events = $eventRepository->filterBySelection($user, $eventSearch, $campus);
            $isFiltered = true;

        }
        return $this->render('event/list.html.twig', [
            'events' => $events,
            'eventAll' => $eventsAll,
            'filterForm' => $filterForm,
            'user' => $user,
            'isFiltered' => $isFiltered,
        ]);

    }

    #[Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detail(Event $event): Response
    {
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
        /** @var User $user */
        $user = $this->getUser();
        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);

        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()) {
            $buttonClicked = $eventForm->getClickedButton();
            $action = $buttonClicked?->getName() ?? 'save'; // Renvoie save par défaut si null pour éviter l'erreur
            $imageFile = $eventForm->get('eventPhoto')->getData();
            $eventManager->handleEvent($event, $user, $imageFile, $action);

            $this->addFlash('success', 'Event created!');

            return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);
        }

        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm->createView(),
        ]);
    }

    #[Route('/new/location', name: 'location_create', methods: ['GET', 'POST'])]
    public function createNewEventLocation(Request $request): Response
    {
        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location);
        $locationForm->handleRequest($request);

        if ($locationForm->isSubmitted() && $locationForm->isValid()) {
            $created = $this->locationManager->handleLocation($location);

            if (!$created) {
                $this->addFlash('error', 'Location already exists!');
            } else {
                $this->addFlash('success', 'Location created!');
            }

            return $this->redirectToRoute('events_create');
        }
        return $this->render('event/newLocation.html.twig', [
            'locationForm' => $locationForm
        ]);
    }

    #[Route('/edit/{id}', name: 'edit', requirements: ['id' => '\d+'])]
    public function editEvent(
        Event        $event,
        Request      $request,
        EventManager $eventManager
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$this->isGranted('EVENT_EDIT', $event)) {
            $this->addFlash('danger', 'You do not have permission to modify this event.');
            return $this->redirectToRoute('events_listFilters');
        }

        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);
        if ($eventForm->isSubmitted() && $eventForm->isValid()) {
            $buttonClicked = $eventForm->getClickedButton();
            $action = $buttonClicked?->getName() ?? 'save'; // Renvoie save par défaut si null pour éviter l'erreur
            $imageFile = $eventForm->get('eventPhoto')->getData();
            $eventManager->handleEvent($event, $user, $imageFile, $action);
            $this->addFlash('success', 'Event edited!');

            return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);
        }
        return $this->render('event/edit.html.twig', [
            'eventFormEdit' => $eventForm->createView()
        ]);
    }

    #[Route('/cancel/{id}', name: 'cancelled', requirements: ['id' => '\d+'])]
    public function cancelEvent(
        Event         $event,
        Request       $request,
        StatusManager $statusManager
    ): Response
    {

        if (!$this->isGranted('EVENT_EDIT', $event)) {
            $this->addFlash('danger', 'You do not have permission to modify this event.');
            return $this->redirectToRoute('events_listFilters');
        }

        if ($event->getEventStatus()->getLabel() === "Canceled") {
            $this->addFlash('warning', 'Event is already cancelled!');
            return $this->redirectToRoute('events_listFilters');
        }

        $form = $this->createForm(CancelledEventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $statusManager->cancelEvent($event);

            $this->addFlash('success', 'Event cancelled!');
            return $this->redirectToRoute('events_detail', ['id' => $event->getId()]);

        }

        return $this->render('event/cancelledEvent.html.twig', [
            'eventFormCancelled' => $form->createView()
        ]);
    }

    #[Route('/{id}/register', name: 'register', requirements: ['id' => '\d+'])]
    public function registerEvent(Event $event, EventRegistrationManager $eventRegistrationMananger): Response
    {
        $user = $this->getUser();

        /** @var User $user */
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
        /** @var User $user */
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
