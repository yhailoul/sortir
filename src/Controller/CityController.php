<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\FilterSearchType;
use App\Form\Model\FilterSearch;
use App\Form\VilleType;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CityController extends AbstractController
{
    #[Route('/city', name: 'city_list', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function cityList(Request $request,CityRepository $cityRepository, EntityManagerInterface $entityManager): Response{
        $city = new City();
        $form = $this->createForm(VilleType::class, $city);
        $form->handleRequest($request);

        $searchedCities = [];
        $cities = $cityRepository->findAll();

        $filterSearch = new FilterSearch();
        $filterForm = $this->createForm(FilterSearchType::class, $filterSearch);
        $filterForm->handleRequest($request);

//        //newcity
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();
            return $this->redirectToRoute('city_list', [], Response::HTTP_SEE_OTHER);
        }
        //search city
        if ($filterForm->isSubmitted()) {
            $searchedCities = $cityRepository->SearchByName($filterSearch);
        }

        return $this->render('city/cityList.html.twig', [
            'searchedCities'=>$searchedCities,
            'cities' => $cities,
            'newCityForm' => $form,
            'filterSearch'=> $filterForm
        ]);
    }
    #[Route('/city/new-city', name: 'city_new', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function newCity(Request $request,
                            EntityManagerInterface $entityManager): Response
    {
        $city = new City();
        $form = $this->createForm(VilleType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();
            $this->addFlash('success', 'City has been created.');
            return $this->redirectToRoute('app_user_cityList', [], Response::HTTP_SEE_OTHER);
        }return $this->render('city/cityList.html.twig', ['newCityForm'=>$form]);
    }

    #[Route('/delete/{id}', name: 'city_delete', requirements: ['id' => '\d+'])]
    #[isGranted('ROLE_ADMIN')]
    public function deleteEvent(
        int                    $id,
        CityRepository        $repository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $city = $repository->find($id);

        if (!$city) {
            throw $this->createNotFoundException('There is no city with the name ' . $city->getName());
        }

        $entityManager->remove($city);
        $entityManager->flush();

        $this->addFlash('success', 'city deleted!');
        return $this->redirectToRoute('city_list');
    }
    #[Route('/edit/{id}', name: 'city_edit', requirements: ['id' => '\d+'])]
    #[isGranted('ROLE_ADMIN')]
    public function editCity(
        int                    $id,
        CityRepository         $repository,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $city = $repository->find($id);
        $cityForm = $this->createForm(VilleType::class, $city);
        $cityForm->handleRequest($request);

        if ($cityForm->isSubmitted() && $cityForm->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();
            $this->addFlash('success', 'city modified!');
            return $this->redirectToRoute('city_list');
        }
        return $this->render('city/city_edit.html.twig', [
            'city' => $city,
            'form' => $cityForm,
        ]);
    }
}
