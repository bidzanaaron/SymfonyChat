<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\UserBiographyInfoType;
use App\Form\UserPersonalInfoType;
use App\Form\UserProfilePictureType;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    private static string $PROFILE_PICTURE_PATH = __DIR__ . '/../../public/uploads/profilePictures/';

    #[Route('/settings', name: 'app_settings')]
    public function index(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();
        $personalInformationForm = $this->createForm(UserPersonalInfoType::class, $user);

        $personalInformationForm->handleRequest($request);

        if ($personalInformationForm->isSubmitted() && $personalInformationForm->isValid()) {
            if (!$user) {
                return $this->redirectToRoute('app_settings');
            }

            $uow = $entityManager->getUnitOfWork();
            $uow->computeChangeSets();
            $changeSet = $uow->getEntityChangeSet($user);

            if (isset($changeSet['username'])) {
                $user->setVerified(false);
            }

            $uow->recomputeSingleEntityChangeSet(
                $entityManager->getClassMetadata(User::class),
                $user
            );

            $entityManager->flush();

            $this->addFlash('success', 'Your personal information has been updated.');

            return $this->redirectToRoute('app_settings');
        }

        $changePasswordForm = $this->createForm(ChangePasswordType::class);
        $changePasswordForm->handleRequest($request);

        if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
            $currentPassword = $changePasswordForm->get('currentPassword')->getData();
            $newPassword = $changePasswordForm->get('newPassword')->getData();
            $confirmNewPassword = $changePasswordForm->get('confirmNewPassword')->getData();

            if (!password_verify($currentPassword, $user->getPassword())) {
                $this->addFlash('errorPwd', 'Your current password is incorrect.');
                return $this->redirectToRoute('app_settings');
            }

            if (strlen($newPassword) < 6 || strlen($newPassword) > 20) {
                $this->addFlash('errorPwd', 'Your new password must be between 6 and 20 characters long.');
                return $this->redirectToRoute('app_settings');
            }

            if ($newPassword !== $confirmNewPassword) {
                $this->addFlash('errorPwd', 'Your new passwords do not match.');
                return $this->redirectToRoute('app_settings');
            }

            $user->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));

            $entityManager->flush();

            $this->addFlash('successPwd', 'Your password has been updated.');

            return $this->redirectToRoute('app_settings');
        }

        return $this->render('settings/index.html.twig', [
            'personalInformationForm' => $personalInformationForm->createView(),
            'changePasswordForm' => $changePasswordForm->createView(),
        ]);
    }

    #[Route('/settings/profile', name: 'app_settings_profile')]
    public function profile(Request $request, Security $security, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $security->getUser();

        $biographyForm = $this->createForm(UserBiographyInfoType::class, $currentUser);
        $biographyForm->handleRequest($request);

        if ($biographyForm->isSubmitted() && $biographyForm->isValid()) {
            $currentUser->setUpdatedAt(new DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Your biography has been updated.');

            return $this->redirectToRoute('app_settings_profile');
        }

        $profilePictureForm = $this->createForm(UserProfilePictureType::class);
        $profilePictureForm->handleRequest($request);

        if ($profilePictureForm->isSubmitted() && $profilePictureForm->isValid()) {
            $file = $profilePictureForm->get('picture')->getData();

            if (!$file) {
                return $this->redirectToRoute('app_settings_profile');
            }

            $newFilename = uniqid("", true) . '.' . $file->guessExtension();

            try {
                $file->move(
                    realpath(self::$PROFILE_PICTURE_PATH) . "/",
                    $newFilename
                );
            } catch (FileException $e) {
                return $this->redirectToRoute('app_settings_profile');
            }

            if ($currentUser) {
                $previousProfileUrl = $currentUser->getProfilePicture();
                if (!empty($previousProfileUrl)) {
                    $explodedPrevious = explode("uploads/profilePictures/", $previousProfileUrl, 2);
                    $explodedPreviousFilename = end($explodedPrevious);

                    if (!unlink(realpath(self::$PROFILE_PICTURE_PATH) . "/" . $explodedPreviousFilename)) {
                        return $this->redirectToRoute('app_settings_profile');
                    }
                }

                $currentUser->setProfilePicture("uploads/profilePictures/" . $newFilename);
                $currentUser->setUpdatedAt(new DateTimeImmutable());

                $entityManager->flush();

                $this->addFlash('profilePictureSuccess', 'Your profile picture has been successfully updated!');
            }

            return $this->redirectToRoute("app_settings_profile");
        }

        return $this->render('settings/profile.html.twig', [
            'biographyForm' => $biographyForm->createView(),
            'profilePictureForm' => $profilePictureForm->createView(),
        ]);
    }
}