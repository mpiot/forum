<?php

/*
 * Copyright 2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Controller;

use App\Events;
use App\Form\ForgetPasswordType;
use App\Form\PasswordResetType;
use App\Form\RegistrationType;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", methods={"GET", "POST"}, name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            // redirect authenticated users to homepage
            return $this->redirectToRoute('category_index');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/registration", name="registration", methods="GET|POST")
     */
    public function registration(UserManager $userManager, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $user = $userManager->createUser();
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->generateToken($user);
            $userManager->updateUser($user);

            $event = new GenericEvent($user);
            $eventDispatcher->dispatch(Events::USER_REGISTERED, $event);

            $this->addFlash('success', 'You have been successfully registered, please valid you email before login.');

            return $this->redirectToRoute('login');
        }

        return $this->render('security/registration/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/activate/{confirmationToken}", name="user_activation", methods="GET")
     */
    public function activation(string $confirmationToken, UserManager $userManager): Response
    {
        $user = $userManager->findUserByConfirmationToken($confirmationToken);

        if (null === $user) {
            $this->addFlash('danger', 'This activation token is invalid.');

            return $this->redirectToRoute('category_index');
        }

        $user->setActivated(true);
        $userManager->removeToken($user);
        $userManager->updateUser($user);

        $this->addFlash('success', 'Your account has been successfully activated.');

        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/request-reset-password", name="user_reset_password_request", methods="GET|POST")
     */
    public function askResetPassword(UserManager $userManager, Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $form = $this->createForm(ForgetPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userManager->findUserByEmail($form->getData()['email']);

            if (null !== $user) {
                $userManager->generateToken($user);
                $userManager->updateUser($user);

                $event = new GenericEvent($user);
                $eventDispatcher->dispatch(Events::USER_RESET, $event);
            }

            $this->addFlash('success', 'An email with a reset password link has been successfully sent.');

            return $this->redirectToRoute('login');
        }

        return $this->render('security/reset_password/request_reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset-password/{confirmationToken}", name="user_reset_password", methods="GET|POST")
     */
    public function resetPassword(string $confirmationToken, UserManager $userManager, Request $request): Response
    {
        $user = $userManager->findUserByConfirmationToken($confirmationToken);

        if (null === $user) {
            $this->addFlash('danger', 'This token is invalid.');

            return $this->redirectToRoute('category_index');
        }

        $form = $this->createForm(PasswordResetType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            $this->addFlash('success', 'Your email has been successfully edited.');

            return $this->redirectToRoute('login');
        }

        return $this->render('security/reset_password/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
