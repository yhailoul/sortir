<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\AvatarService;
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
    #[isGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);

    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function new(Request                     $request,
                        EntityManagerInterface      $entityManager,
                        UserPasswordHasherInterface $userPasswordHasher,
                        FileUploader                $fileUploader,
                        AvatarService               $defaultAvatar): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            $user->setRoles(['ROLE_USER']);
            $user->setActive(true);
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $this->handleFileUploads($user, $form, $fileUploader);
            $defaultAvatar->correctionPhotoProfile($user);

            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(
        User                   $user,
        EntityManagerInterface $entityManager,
        AvatarService          $defaultAvatar): Response
    {
        if (!$user->getPhoto()) {
            $defaultAvatar->correctionPhotoProfile($user);
            $entityManager->flush();
        }

        $photoPath = $defaultAvatar->resolvePhotoPath($user->getPhoto());

        return $this->render('user/show.html.twig', [
            'user' => $user,
            'photoPath' => $photoPath,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request                     $request,
                         Security                    $security,
                         int                         $id,
                         UserRepository              $userRepository,
                         EntityManagerInterface      $entityManager,
                         FileUploader                $fileUploader,
                         UserPasswordHasherInterface $passwordHasher,
                         AvatarService               $defaultAvatar): Response
    {
        $user = $userRepository->find($id);
        $authUser = $security->getUser();
        if ($user === $authUser) {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $checkPassword = $form->get('password')->getData();

                if (!empty($checkPassword)) {
                    $security->login($user);
                    $hashedPassword = $passwordHasher->hashPassword($user, $checkPassword);
                    $user->setPassword($hashedPassword);
                }

                $defaultAvatar->correctionPhotoProfile($user);
                $this->handleFileUploads($user, $form, $fileUploader);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        } else {
            throw $this->createAccessDeniedException('You are not allowed to edit this user.');
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/activate/admin', name: 'app_user_activate_admin', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function Activate(
        EntityManagerInterface $entityManager,
        UserRepository         $repository,
        int                    $id): Response
    {
        $user = $repository->find($id);
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }

        if (!$user->isActive()) {
            $user->setActive(true);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'You have activated user:' . $user->getUsername());
        } else {
            $this->addFlash('warning', 'This user is already active');
        }


        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/deactivate/admin', name: 'app_user_deactivate_admin', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function Deactivate(Security               $security,
                               EntityManagerInterface $entityManager,
                               UserRepository         $repository,
                               int                    $id): Response
    {
        $user = $repository->find($id);
        $authUser = $security->getUser();
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }
        if ($user === $authUser) {
            $this->addFlash('warning', 'You cannot deactivate your own account');
            return $this->redirectToRoute('app_user_index');
        }
        if ($user->isActive()) {
            $user->setActive(false);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'You have deactivated user:' . $user->getUsername());
        } else {
            $this->addFlash('warning', 'This user is already deactivated');
        }
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/delete/{id}', name: 'app_user_delete', methods: ['GET', 'POST'])]
    #[isGranted('ROLE_ADMIN')]
    public function delete(
        int                    $id,
        UserRepository         $repository,
        Security               $security,
        EntityManagerInterface $entityManager): Response
    {
        $user = $repository->find($id);
        $authUser = $security->getUser();
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }
        if ($user === $authUser) {
            $this->addFlash('warning', 'You cannot delete your own account');
            return $this->redirectToRoute('app_user_index');
        } else {
            $entityManager->remove($user);
            $entityManager->flush();
            $this->addFlash('success', 'You have deleted user:' . $user->getUsername());
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
