<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends AbstractController
{
    #[Route('/settings', name: 'app_settings')]
    public function index(): Response
    {
        return $this->render('settings/index.html.twig');
    }

    #[Route('/settings/change-password', name: 'app_settings_change_password')]
    public function changePassword(Security $security, Request $request, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $security->getUser();

        $currentPassword = $request->get('currentPassword');
        $newPassword = $request->get('newPassword');
        $confirmNewPassword = $request->get('confirmNewPassword');

        if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
            $this->addFlash('errorPwd', 'Please fill in all fields.');
            return $this->redirectToRoute('app_settings');
        }

        if (!password_verify($currentPassword, $currentUser->getPassword())) {
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

        $currentUser->setPassword(password_hash($newPassword, PASSWORD_DEFAULT));

        $entityManager->persist($currentUser);
        $entityManager->flush();

        $this->addFlash('successPwd', 'Your password has been updated.');

        return $this->redirectToRoute('app_settings');
    }

    #[Route('/settings/change-information', name: 'app_settings_change_information')]
    public function changeInformation(Security $security, Request $request, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $security->getUser();

        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        $username = $request->get('username');
        $email = $request->get('email');

        if ($firstName === $currentUser->getFirstname() && $lastName === $currentUser->getLastname() && $username === $currentUser->getUsername() && $email === $currentUser->getEmail()) {
            $this->addFlash('error', 'You have not changed any settings.');
            return $this->redirectToRoute('app_settings');
        }

        if (empty($firstName) || empty($lastName) || empty($username) || empty($email)) {
            $this->addFlash('error', 'Please fill in all fields.');
            return $this->redirectToRoute('app_settings');
        }

        if (preg_match('/\s/', $firstName) || preg_match('/\s/', $lastName)) {
            $this->addFlash('error', 'Your first name and last name cannot contain spaces.');
            return $this->redirectToRoute('app_settings');
        }

        if (!preg_match('/^[a-zA-Z]+$/', $firstName) || !preg_match('/^[a-zA-Z]+$/', $lastName)) {
            $this->addFlash('error', 'Your first name and last name can only contain letters.');
            return $this->redirectToRoute('app_settings');
        }

        if (strlen($username) < 3 || strlen($username) > 20) {
            $this->addFlash('error', 'Your username must be between 3 and 20 characters long.');
            return $this->redirectToRoute('app_settings');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('error', 'Please enter a valid email address.');
            return $this->redirectToRoute('app_settings');
        }

        if (preg_match('/^[0-9_]+$/', $username)) {
            $this->addFlash('error', 'Your username cannot be just numbers or just underscores.');
            return $this->redirectToRoute('app_settings');
        }

        if (str_starts_with($username, "_")) {
            $this->addFlash('error', 'Your username cannot start with an underscore.');
            return $this->redirectToRoute('app_settings');
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $this->addFlash('error', 'Your username can only contain letters, numbers and underscores.');
            return $this->redirectToRoute('app_settings');
        }

        if ($username !== $currentUser->getUsername()) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($user) {
                $this->addFlash('error', 'This username is already taken.');
                return $this->redirectToRoute('app_settings');
            }
        }

        if ($email !== $currentUser->getEmail()) {
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user) {
                $this->addFlash('error', 'This email address is already taken.');
                return $this->redirectToRoute('app_settings');
            }
        }

        $currentUser->setFirstname($firstName);
        $currentUser->setLastname($lastName);
        $currentUser->setUsername($username);
        $currentUser->setEmail($email);

        $entityManager->persist($currentUser);
        $entityManager->flush();

        $this->addFlash('success', 'Your settings have been updated.');

        return $this->redirectToRoute('app_settings');
    }
}