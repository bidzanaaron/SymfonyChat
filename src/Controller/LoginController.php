<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

          return $this->render('login/index.html.twig', [
              'controller_name' => 'LoginController',
              'last_username'   => $lastUsername,
              'error'           => $error,
          ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(Security $security): Response
    {
        $security->logout();

        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify', name: 'app_verify_email')]
    public function verifyEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        $id = $request->get('id');
        $user = $userRepository->find($id);

        if (!$user) {
            $this->addFlash('error', 'User not found.');

            return $this->redirectToRoute('app_login');
        }

        try {
            $verifyEmailHelper->validateEmailConfirmation(
                $request->getUri(),
                $user->getId(),
                $user->getEmail(),
            );
        } catch (VerifyEmailExceptionInterface $e) {
            $this->addFlash('error', $e->getReason());

            return $this->redirectToRoute('app_login');
        }

        $user->setEmailVerified(true);
        $entityManager->flush();

        $this->addFlash('success', 'Account Verified! You can now log in.');
        return $this->redirectToRoute('app_login');
    }
}