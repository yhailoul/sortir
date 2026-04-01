<?php

namespace App\Controller;


use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Security\Voter\UserVoter;
use App\Service\AvatarService;
use App\Service\FileUploader;
use App\Service\UserCsvImporter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/user')]
final class UserController extends AbstractController
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    #[Route(name: 'app_user_index', methods: ['GET'])]
    #[isGranted('ROLE_ADMIN')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);

    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request                     $request,
                        EntityManagerInterface      $entityManager,
                        UserPasswordHasherInterface $userPasswordHasher,
                        FileUploader                $fileUploader,
                        AvatarService               $defaultAvatar,
                        UserCsvImporter             $csvImporter): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::CREATE, null);

        $user = new User();

        $uploadedFiles = $request->files->get('user');
        $hasCsv = !empty($uploadedFiles['csvFile']);

        $validationGroups = $hasCsv
            ? ['Default']                       // CSV : on valide uniquement le champ csvFile
            : ['Default', 'manual_creation'];   // Manuel : on valide tous les champs

        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted()) {

            $csvFile = $userForm->get('csvFile')->getData();

            if ($csvFile) {
                $csvViolations = $this->validator->validate(
                    $csvFile,
                    new \Symfony\Component\Validator\Constraints\File(
                        mimeTypes: ['text/csv', 'text/plain', 'application/csv', 'application/excel', 'application/vnd.msexcel'],
                        mimeTypesMessage: 'The file must be a CSV file.',
                    )
                );

                if ($csvViolations->count() === 0) {
                    $result = $csvImporter->import($csvFile->getPathname());
                    $this->addFlash('success', "{$result['created']} Users imported successfully.");
                    foreach ($result['errors'] as $error) {
                        $this->addFlash('warning', $error);
                    }
                    return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
                }

            } else if ($userForm->isValid()) {
                $plainPassword = $userForm->get('password')->getData();
                $user->setRoles(['ROLE_USER']);
                $user->setActive(true);
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
                $this->handleFileUploads($user, $userForm, $fileUploader);
                $defaultAvatar->correctionPhotoProfile($user);
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'User created successfully.');
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $userForm,
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
                         EntityManagerInterface      $entityManager,
                         FileUploader                $fileUploader,
                         UserPasswordHasherInterface $passwordHasher,
                         User                        $user,
                         AvatarService               $defaultAvatar): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::EDIT, $user);

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $checkPassword = $form->get('password')->getData();

            if (!empty($checkPassword)) {
                $hashedPassword = $passwordHasher->hashPassword($user, $checkPassword);
                $user->setPassword($hashedPassword);
                $security->login($user);
            }

            $defaultAvatar->correctionPhotoProfile($user);
            $this->handleFileUploads($user, $form, $fileUploader);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/activate/admin', name: 'app_user_activate_admin', methods: ['GET', 'POST'])]
    public function activate(
        EntityManagerInterface $entityManager,
        User                   $user): Response
    {

        $this->denyAccessUnlessGranted(UserVoter::ACTIVATE, $user);

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
    public function disable(EntityManagerInterface $entityManager,
                               User                   $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::DISABLE, $user);

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
    public function delete(
        User                   $user,
        EntityManagerInterface $entityManager): Response
    {

        $this->denyAccessUnlessGranted(UserVoter::DELETE, $user);

        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'You have deleted user:' . $user->getUsername());


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
