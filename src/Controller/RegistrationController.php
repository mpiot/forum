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

use App\Entity\User;
use App\Events;
use App\Form\RegistrationType;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/registration")
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/", name="registration", methods="GET|POST")
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

        return $this->render('registration/registration.html.twig', [
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
}
