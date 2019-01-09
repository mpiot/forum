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

use App\Form\ChangePasswordType;
use App\Form\DeleteAccountType;
use App\Form\ProfileType;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ProfileController extends AbstractController
{
    /**
     * @Route("/my-profile", name="profile_show", methods="GET")
     */
    public function show(): Response
    {
        return $this->render('profile/show.html.twig');
    }

    /**
     * @Route("/my-profile/edit", name="profile_edit", methods="GET|POST")
     */
    public function edit(Request $request, UserManager $userManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);
            $this->addFlash('success', 'Your profile has been successfully edited.');

            return $this->redirectToRoute('profile_show');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/my-profile/edit-password", name="profile_edit_password", methods="GET|POST")
     */
    public function editPassword(Request $request, UserManager $userManager): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->updateUser($user);

            $this->addFlash('success', 'Your password has been successfully edited.');

            return $this->redirectToRoute('profile_show');
        }

        return $this->render('profile/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/my-profile/delete", name="profile_delete", methods="GET|POST")
     */
    public function delete(Request $request, UserManager $userManager, TokenStorageInterface $tokenStorage): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(DeleteAccountType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userManager->deleteUser($user);

            $this->addFlash('success', 'Your profile has been successfully deleted.');

            // To avoid an error with the Session
            $tokenStorage->setToken(null);
            $request->getSession()->invalidate();

            return $this->redirectToRoute('category_index');
        }

        return $this->render('profile/delete_account.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
