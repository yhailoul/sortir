<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\User;
use App\Form\UserType;
use App\Form\VilleType;
use App\Repository\CityRepository;
use App\Repository\UserRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
final class UserController extends AbstractController
{
    #[Route(name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);

    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]

    public function new(Request $request,
                        EntityManagerInterface $entityManager,
                        UserPasswordHasherInterface $userPasswordHasher,
                        FileUploader $fileUploader): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formDataPassword = $form->getData()->getPassword();
            dump($form->getData());
            //dd($formDataPassword);
            $plainPassword = $form->get('password')->getData();
            //dd($plainPassword);
            $user->setRoles(['ROLE_USER']);
            $user->setActive(true);
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $this->handleFileUploads($user, $form, $fileUploader);
            $entityManager->persist($user);
            $entityManager->flush();
            //dd($plainPassword);
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/city', name: 'app_user_cityList', methods: ['GET', 'POST'])]
    #[IsGranted("ROLE_ADMIN")]
    public function cityList(Request $request,CityRepository $cityRepository, EntityManagerInterface $entityManager): Response{
        $city = new City();
        $form = $this->createForm(VilleType::class, $city);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($city);
            $entityManager->flush();
            return $this->redirectToRoute('app_user_cityList', [], Response::HTTP_SEE_OTHER);
        }
        $cities = $cityRepository->findAll();
        return $this->render('user/cityList.html.twig', [
            'cities' => $cities,
            'newCityForm' => $form,
        ]);
    }
    #[Route('/city/new-city', name: 'app_user_newCity', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function newCity(Request $request,
                            EntityManagerInterface $entityManager): Response
    {
        $city = new City();
        $form = $this->createForm(VilleType::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           // $formDataCity = $form->getData()->getCity();
            $entityManager->persist($city);
            $entityManager->flush();
            $this->addFlash('success', 'City has been created.');
            return $this->redirectToRoute('app_user_cityList', [], Response::HTTP_SEE_OTHER);
        }return $this->render('user/cityList.html.twig', ['newCityForm'=>$form]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,
                         Security $security,
                         User $user,
                         int $id,
                         UserRepository $userRepository,
                         EntityManagerInterface $entityManager,
                         FileUploader $fileUploader,
                         UserPasswordHasherInterface $passwordHasher): Response
    {
        $user=$userRepository->find($id);
        $authUser = $security->getUser();
        if($user === $authUser){
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $checkPassword = $form->get('password')->getData();

                if (!empty($checkPassword)) {
                    $security->login($user);
                    $hashedPassword = $passwordHasher->hashPassword($user, $checkPassword);
                    $user->setPassword($hashedPassword);

                }

                $this->handleFileUploads($user, $form, $fileUploader);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }else{
            throw $this->createAccessDeniedException('You are not allowed to edit this user.');
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/{id}/activate/admin', name: 'app_user_activate_admin', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function Activate(Request $request,
                                         Security $security,
                                         User $user,
                                         EntityManagerInterface $entityManager,
                                         UserRepository $repository,
                                         int $id): Response
    {
        $user= $repository->find($id);
        if(!$user){
            throw $this->createNotFoundException("User not found");
        }

            if(!$user->isActive()){
                $user->setActive(true);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'You have activated user:'.$user->getUsername());
            }else{
                $this->addFlash('warning', 'This user is already active');
            }


        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{id}/deactivate/admin', name: 'app_user_deactivate_admin', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function Deactivate(Security $security,
                                 EntityManagerInterface $entityManager,
                                 UserRepository $repository,
                                 int $id): Response
    {
        $user= $repository->find($id);
        $authUser = $security->getUser();
        if(!$user){
            throw $this->createNotFoundException("User not found");
        }
        if($user === $authUser){
            $this->addFlash('warning', 'You cannot deactivate your own account');
            return $this->redirectToRoute('app_user_index');
        }
            if($user->isActive()){
                $user->setActive(false);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'You have deactivated user:'.$user->getUsername());
            }else{
                $this->addFlash('warning', 'This user is already deactivated');
            }
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/delete/{id}', name: 'app_user_delete', methods: ['GET','POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function delete(int $id,
                           UserRepository $repository,
                           Security $security,
                           EntityManagerInterface $entityManager): Response
    {
        $user= $repository->find($id);
        $authUser = $security->getUser();
        if(!$user){
            throw $this->createNotFoundException("User not found");
        }
        if($user === $authUser){
            $this->addFlash('warning', 'You cannot delete your own account');
            return $this->redirectToRoute('app_user_index');
        }else{
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'You have deleted user:'.$user->getUsername());
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    private function handleFileUploads(User $user, $form, FileUploader $fileUploader): void
    {
        $imageFile = $form->get('photo')->getData();
        if ($imageFile) {
            $user->setPhoto($fileUploader->upload($imageFile));
        }
    }
}
