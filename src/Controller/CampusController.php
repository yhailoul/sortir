<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Form\FilterSearchType;
use App\Form\Model\FilterSearch;
use App\Form\VilleType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CampusController extends AbstractController
{
    #[Route('/campus', name: 'campus_list', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function campusList(Request $request,
                               CampusRepository $repository,
                               EntityManagerInterface $entityManager): Response{

        $searchedCampus= [];
        $campusList= $repository->findAll();

        $filterSearch = new FilterSearch();
        $filterForm = $this->createForm(FilterSearchType::class, $filterSearch);
        $filterForm->handleRequest($request);

        //search campus
        if ($filterForm->isSubmitted()) {
            $searchedCampus = $repository->SearchByName($filterSearch);
        }

        return $this->render('campus/campusList.html.twig', [
            'searchedCampus'=>$searchedCampus,
            'campus' => $campusList,
            'filterSearch'=> $filterForm
        ]);
    }


    #[Route('/campus/delete/{id}', name: 'campus_delete', requirements: ['id' => '\d+'])]
    #[isGranted('ROLE_ADMIN')]
    public function deleteEvent(
        int                    $id,
        CampusRepository        $repository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $campus = $repository->find($id);

        if (!$campus) {
            throw $this->createNotFoundException('There is no campus with the name ' . $campus->getName());
        }

        $entityManager->remove($campus);
        $entityManager->flush();

        $this->addFlash('success', 'campus deleted!');
        return $this->redirectToRoute('campus_list');
    }
    #[Route('/campus/edit/{id}', name: 'campus_edit', requirements: ['id' => '\d+'])]
    #[isGranted('ROLE_ADMIN')]
    public function editCampus(
        int                    $id,
        CampusRepository         $repository,
        Request                $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $campus = $repository->find($id);
        $campusForm = $this->createForm(CampusType::class, $campus);
        $campusForm->handleRequest($request);

        if ($campusForm->isSubmitted() && $campusForm->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();
            $this->addFlash('success', 'campus modified!');
            return $this->redirectToRoute('campus_list');
        }
        return $this->render('campus/campus_edit.html.twig', [

            'campus' => $campus,
            'campusForm' => $campusForm,
        ]);
    }
}
